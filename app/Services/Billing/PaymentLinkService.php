<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

/**
 * Gera e resolve links públicos de pagamento. O cliente abre o link sem login
 * e paga por ele. O token em claro só existe na URL; no banco fica o hash SHA-256.
 */
final class PaymentLinkService
{
    private const EXPIRA_PADRAO_DIAS = 30;

    /**
     * Cria um link de pagamento para um cliente + plano.
     *
     * @return array{id:int, token:string, url:string}
     */
    public function gerar(
        int $clienteId,
        int $planoId,
        array $addons = [],
        int $periodo = 1,
        string $currency = 'BRL',
        ?int $createdBy = null,
        int $expiraDias = self::EXPIRA_PADRAO_DIAS,
    ): array {
        $pdo = BancoDeDados::pdo();

        // Validar cliente e plano
        $c = $pdo->prepare('SELECT id FROM clients WHERE id = :id');
        $c->execute([':id' => $clienteId]);
        if (!$c->fetch()) {
            throw new \RuntimeException('Cliente não encontrado.');
        }

        $p = $pdo->prepare("SELECT id FROM plans WHERE id = :id AND status = 'active'");
        $p->execute([':id' => $planoId]);
        if (!$p->fetch()) {
            throw new \RuntimeException('Plano inválido ou inativo.');
        }

        $currency = strtoupper(trim($currency)) === 'USD' ? 'USD' : 'BRL';
        $periodo = $periodo >= 12 ? 12 : ($periodo >= 6 ? 6 : 1);

        $token = bin2hex(random_bytes(32)); // 64 chars hex
        $tokenHash = hash('sha256', $token);
        $expiresAt = $expiraDias > 0 ? date('Y-m-d H:i:s', time() + $expiraDias * 86400) : null;

        $pdo->prepare(
            'INSERT INTO payment_links
               (client_id, plan_id, addons_json, periodo, currency, token_hash, token_hint, status, created_by, expires_at, created_at)
             VALUES (:c, :p, :aj, :per, :cur, :th, :hint, :st, :by, :exp, :cr)'
        )->execute([
            ':c' => $clienteId,
            ':p' => $planoId,
            ':aj' => !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null,
            ':per' => $periodo,
            ':cur' => $currency,
            ':th' => $tokenHash,
            ':hint' => substr($token, -6),
            ':st' => 'pending',
            ':by' => $createdBy,
            ':exp' => $expiresAt,
            ':cr' => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $pdo->lastInsertId();
        $url = rtrim(ConfiguracoesSistema::appUrlBase(), '/') . '/pagar/' . $token;

        return ['id' => $id, 'token' => $token, 'url' => $url];
    }

    /**
     * Gera um link de RENOVAÇÃO para uma assinatura já existente (PIX/boleto não têm
     * cobrança automática no cartão, então o cliente precisa pagar a cada ciclo).
     * O link já vem vinculado à subscription, de modo que ao abri-lo o cliente vê
     * direto a cobrança pendente que o Asaas gerou — sem criar nova assinatura.
     *
     * Gera sempre um token novo (o token em claro não é recuperável, só o hash é
     * persistido). Para não acumular lixo, expira os links de renovação pendentes
     * anteriores da mesma assinatura antes de criar o novo.
     *
     * @return array{id:int, token:string, url:string}|null
     */
    public function gerarRenovacao(int $subscriptionId, int $expiraDias = 15): ?array
    {
        $pdo = BancoDeDados::pdo();

        $st = $pdo->prepare('SELECT id, client_id, plan_id FROM subscriptions WHERE id = :id LIMIT 1');
        $st->execute([':id' => $subscriptionId]);
        $sub = $st->fetch();
        if (!is_array($sub) || (int) ($sub['plan_id'] ?? 0) <= 0 || (int) ($sub['client_id'] ?? 0) <= 0) {
            return null;
        }

        // Expira links de renovação pendentes anteriores desta mesma assinatura,
        // para não deixar vários tokens válidos simultâneos apontando ao mesmo pagamento.
        $pdo->prepare("UPDATE payment_links SET status = 'expired' WHERE subscription_id = :s AND status = 'pending'")
            ->execute([':s' => $subscriptionId]);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + $expiraDias * 86400);

        $pdo->prepare(
            'INSERT INTO payment_links
               (client_id, plan_id, periodo, currency, token_hash, token_hint, status, subscription_id, expires_at, created_at)
             VALUES (:c, :p, 1, :cur, :th, :hint, :st, :sid, :exp, :cr)'
        )->execute([
            ':c' => (int) $sub['client_id'],
            ':p' => (int) ($sub['plan_id'] ?? 0),
            ':cur' => 'BRL',
            ':th' => $tokenHash,
            ':hint' => substr($token, -6),
            ':st' => 'pending',
            ':sid' => $subscriptionId,
            ':exp' => $expiresAt,
            ':cr' => date('Y-m-d H:i:s'),
        ]);

        $url = rtrim(ConfiguracoesSistema::appUrlBase(), '/') . '/pagar/' . $token;
        return ['id' => (int) $pdo->lastInsertId(), 'token' => $token, 'url' => $url];
    }

    /**
     * Resolve um link pelo token (em claro). Retorna o registro + dados do cliente/plano,
     * ou null se inválido/expirado. Não bloqueia links já pagos (para mostrar a confirmação),
     * mas sinaliza o status.
     *
     * @return array|null
     */
    public function resolverPorToken(string $token): ?array
    {
        $token = trim($token);
        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            return null;
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT pl.*, c.name AS client_name, c.email AS client_email, c.cpf_cnpj AS client_document,
                    p.name AS plan_name, p.price_monthly, p.price_monthly_usd, p.currency AS plan_currency,
                    p.cpu, p.ram, p.storage
             FROM payment_links pl
             INNER JOIN clients c ON c.id = pl.client_id
             INNER JOIN plans p ON p.id = pl.plan_id
             WHERE pl.token_hash = :h LIMIT 1'
        );
        $stmt->execute([':h' => hash('sha256', $token)]);
        $link = $stmt->fetch();
        if (!is_array($link)) {
            return null;
        }

        // Expiração
        $expiresAt = (string) ($link['expires_at'] ?? '');
        if ($expiresAt !== '' && strtotime($expiresAt) < time() && (string) $link['status'] === 'pending') {
            $pdo->prepare("UPDATE payment_links SET status = 'expired' WHERE id = :id AND status = 'pending'")
                ->execute([':id' => (int) $link['id']]);
            $link['status'] = 'expired';
        }

        return $link;
    }

    /**
     * Reivindica o link para processamento de forma ATÔMICA, evitando que dois
     * cliques/abas simultâneos criem duas assinaturas/VPS/cobranças para o mesmo link.
     * Só uma requisição consegue a reivindicação (status pending + sem assinatura).
     *
     * @return bool true se esta chamada obteve o direito de processar
     */
    public function reivindicarParaProcessamento(int $linkId): bool
    {
        $stmt = BancoDeDados::pdo()->prepare(
            "UPDATE payment_links SET status = 'processing'
             WHERE id = :id AND status = 'pending' AND subscription_id IS NULL"
        );
        $stmt->execute([':id' => $linkId]);
        return $stmt->rowCount() === 1;
    }

    /**
     * Libera a reivindicação (volta a pending) caso o processamento falhe,
     * para o cliente poder tentar novamente.
     */
    public function liberarReivindicacao(int $linkId): void
    {
        BancoDeDados::pdo()
            ->prepare("UPDATE payment_links SET status = 'pending' WHERE id = :id AND status = 'processing' AND subscription_id IS NULL")
            ->execute([':id' => $linkId]);
    }

    /**
     * Vincula a assinatura criada ao link e volta o status para pending
     * (aguardando confirmação de pagamento pelo webhook).
     */
    public function vincularAssinatura(int $linkId, int $subscriptionId): void
    {
        BancoDeDados::pdo()
            ->prepare("UPDATE payment_links SET subscription_id = :s, status = 'pending' WHERE id = :id")
            ->execute([':s' => $subscriptionId, ':id' => $linkId]);
    }

    /**
     * Marca o link como pago (usado quando o webhook confirma, ou o cartão aprova na hora).
     */
    public function marcarPago(int $linkId): void
    {
        BancoDeDados::pdo()
            ->prepare("UPDATE payment_links SET status = 'paid', used_at = NOW() WHERE id = :id")
            ->execute([':id' => $linkId]);
    }

    /**
     * Reseta o link para o estado inicial (sem assinatura, pending), permitindo que o
     * cliente escolha outra forma de pagamento. Usado ao "trocar forma de pagamento".
     */
    public function desvincularAssinatura(int $linkId): void
    {
        BancoDeDados::pdo()
            ->prepare("UPDATE payment_links SET subscription_id = NULL, status = 'pending' WHERE id = :id AND status <> 'paid'")
            ->execute([':id' => $linkId]);
    }

    /**
     * @return array<int, array{id:int,name:string,price:float,price_usd:float,price_annual:float,price_annual_usd:float}>
     */
    public function carregarAddons(int $planId, array $addonIds): array
    {
        $addonIds = array_values(array_filter(array_map('intval', $addonIds)));
        if ($addonIds === []) {
            return [];
        }
        $pdo = BancoDeDados::pdo();
        $placeholders = implode(',', array_fill(0, count($addonIds), '?'));
        $st = $pdo->prepare("SELECT id, name, price, price_usd, price_annual, price_annual_usd FROM plan_addons WHERE id IN ($placeholders) AND plan_id = ? AND active = 1");
        $st->execute(array_merge($addonIds, [$planId]));
        $out = [];
        foreach (($st->fetchAll() ?: []) as $r) {
            $out[] = [
                'id' => (int) $r['id'],
                'name' => (string) $r['name'],
                'price' => (float) $r['price'],
                'price_usd' => (float) ($r['price_usd'] ?? 0),
                'price_annual' => (float) ($r['price_annual'] ?? 0),
                'price_annual_usd' => (float) ($r['price_annual_usd'] ?? 0),
            ];
        }
        return $out;
    }
}
