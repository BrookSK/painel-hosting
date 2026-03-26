<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use DateTimeImmutable;
use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\Core\BancoDeDados;

final class AssinaturasService
{
    public function __construct(
        private readonly AsaasApi $asaas,
    ) {
    }

    public function garantirClienteAsaas(int $clientId): string
    {
        $pdo = BancoDeDados::pdo();

        try {
            $stmt = $pdo->prepare('SELECT id, name, email, cpf_cnpj, phone, mobile_phone, asaas_customer_id FROM clients WHERE id = :id');
            $stmt->execute([':id' => $clientId]);
            $c = $stmt->fetch();
        } catch (\Throwable $e) {
            $stmt = $pdo->prepare('SELECT id, name, email, asaas_customer_id FROM clients WHERE id = :id');
            $stmt->execute([':id' => $clientId]);
            $c = $stmt->fetch();
        }

        if (!is_array($c)) {
            throw new \RuntimeException('Cliente não encontrado.');
        }

        $asaasId = (string) ($c['asaas_customer_id'] ?? '');
        if ($asaasId !== '') {
            return $asaasId;
        }

        $dados = [
            'name' => (string) ($c['name'] ?? ''),
            'email' => (string) ($c['email'] ?? ''),
        ];

        $cpf = trim((string) ($c['cpf_cnpj'] ?? ''));
        if ($cpf !== '') {
            $dados['cpfCnpj'] = $cpf;
        }

        $phone = trim((string) ($c['phone'] ?? ''));
        if ($phone !== '') {
            $dados['phone'] = $phone;
        }

        $mobile = trim((string) ($c['mobile_phone'] ?? ''));
        if ($mobile !== '') {
            $dados['mobilePhone'] = $mobile;
        }

        $resp = $this->asaas->criarCliente($dados);
        $novoId = (string) ($resp['id'] ?? '');
        if ($novoId === '') {
            throw new \RuntimeException('Asaas não retornou o id do cliente.');
        }

        $up = $pdo->prepare('UPDATE clients SET asaas_customer_id = :a WHERE id = :id');
        $up->execute([':a' => $novoId, ':id' => $clientId]);

        return $novoId;
    }

    public function criarAssinaturaDoPlano(int $clientId, int $planId, string $billingType, array $addons = []): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, name, price_monthly, cpu, ram, storage FROM plans WHERE id = :id AND status = \'active\'');
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            throw new \RuntimeException('Plano não encontrado.');
        }

        $precoTotal = (float) $plano['price_monthly'];
        foreach ($addons as $a) {
            $precoTotal += (float)($a['price'] ?? 0);
        }
        $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;

        $customerId = $this->garantirClienteAsaas($clientId);

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            try {
                $insVps = $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)');
                $insVps->execute([
                    ':c' => $clientId,
                    ':cpu' => (int) $plano['cpu'],
                    ':ram' => (int) $plano['ram'],
                    ':st' => (int) $plano['storage'],
                    ':s' => 'pending_payment',
                    ':cr' => $agora,
                    ':pid' => (int) $plano['id'],
                ]);
            } catch (\Throwable $e) {
                $insVps = $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr)');
                $insVps->execute([
                    ':c' => $clientId,
                    ':cpu' => (int) $plano['cpu'],
                    ':ram' => (int) $plano['ram'],
                    ':st' => (int) $plano['storage'],
                    ':s' => 'pending_payment',
                    ':cr' => $agora,
                ]);
            }

            $vpsId = (int) $pdo->lastInsertId();

            $due = (new DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d');

            $respAss = $this->asaas->criarAssinatura([
                'customer' => $customerId,
                'billingType' => $billingType,
                'value' => $precoTotal,
                'cycle' => 'MONTHLY',
                'description' => 'Assinatura ' . (string) $plano['name'] . (!empty($addons) ? ' + addons' : ''),
                'nextDueDate' => $due,
            ]);

            $asaasSubId = (string) ($respAss['id'] ?? '');
            if ($asaasSubId === '') {
                throw new \RuntimeException('Asaas não retornou o id da assinatura.');
            }

            try {
                $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, asaas_subscription_id, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, :a, :s, :n, :cr)');
                $insSub->execute([
                    ':c' => $clientId,
                    ':v' => $vpsId,
                    ':p' => (int) $plano['id'],
                    ':aj' => $addonsJson,
                    ':a' => $asaasSubId,
                    ':s' => 'PENDING',
                    ':n' => $due,
                    ':cr' => $agora,
                ]);
            } catch (\Throwable $e) {
                $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, asaas_subscription_id, status, next_due_date, created_at) VALUES (:c, :v, :a, :s, :n, :cr)');
                $insSub->execute([
                    ':c' => $clientId,
                    ':v' => $vpsId,
                    ':a' => $asaasSubId,
                    ':s' => 'PENDING',
                    ':n' => $due,
                    ':cr' => $agora,
                ]);
            }

            $localSubId = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $cobrancas = $this->asaas->listarCobrancasDaAssinatura($asaasSubId);

        return [
            'assinatura' => $respAss,
            'cobrancas' => $cobrancas,
            'local_subscription_id' => $localSubId,
        ];
    }
}
