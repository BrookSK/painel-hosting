<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PlanosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT p.id, p.name, p.cpu, p.ram, p.storage, p.price_monthly, p.status, p.client_id, c.name AS client_name FROM plans p LEFT JOIN clients c ON c.id = p.client_id ORDER BY p.id DESC');
        $planos = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/planos-listar.php', [
            'planos' => is_array($planos) ? $planos : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => '',
            'addons' => [],
            'plano' => [
                'id' => null,
                'name' => '',
                'description' => '',
                'cpu' => 2,
                'ram' => 4 * 1024,
                'storage' => 80 * 1024,
                'price_monthly' => '297.00',
                'backup_slots' => 0,
                'specs_json' => '',
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            return Resposta::texto('Plano não encontrado.', 404);
        }

        $addons = $this->buscarAddons($pdo, $id);

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => '',
            'plano' => $plano,
            'addons' => $addons,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $nome = trim((string) ($req->post['name'] ?? ''));
        $desc = trim((string) ($req->post['description'] ?? ''));
        $cpu = (int) ($req->post['cpu'] ?? 0);
        $ram = (int) ($req->post['ram'] ?? 0);
        $storage = (int) ($req->post['storage'] ?? 0);
        $preco = (string) ($req->post['price_monthly'] ?? '0');
        $stripePriceId = trim((string) ($req->post['stripe_price_id'] ?? ''));
        $specs = trim((string) ($req->post['specs_json'] ?? ''));
        $supportChannels = trim((string) ($req->post['support_channels'] ?? ''));
        $status = (string) ($req->post['status'] ?? 'active');
        $backupSlots = max(0, min(2, (int) ($req->post['backup_slots'] ?? 0)));
        $isFeatured = (int) ($req->post['is_featured'] ?? 0) === 1 ? 1 : 0;
        $clientId = (int) ($req->post['client_id'] ?? 0);
        $clientIdVal = $clientId > 0 ? $clientId : null;

        if ($nome === '' || $cpu <= 0 || $ram <= 0 || $storage <= 0) {
            return $this->renderizarErro($id, $nome, $desc, $cpu, $ram, $storage, $preco, $specs, $supportChannels, $status, 'Preencha os campos obrigatórios.');
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $pdo = BancoDeDados::pdo();

        try {
            if ($id > 0) {
                try {
                    $stmt = $pdo->prepare('UPDATE plans SET name=:n, description=:d, cpu=:c, ram=:r, storage=:s, price_monthly=:p, stripe_price_id=:sp, specs_json=:j, support_channels=:sc, backup_slots=:bs, is_featured=:ft, status=:st WHERE id=:id');
                    $stmt->execute([
                        ':n' => $nome, ':d' => $desc !== '' ? $desc : null,
                        ':c' => $cpu, ':r' => $ram, ':s' => $storage, ':p' => $preco,
                        ':sp' => $stripePriceId !== '' ? $stripePriceId : null,
                        ':j' => $specs !== '' ? $specs : null,
                        ':sc' => $supportChannels !== '' ? $supportChannels : null,
                        ':bs' => $backupSlots, ':ft' => $isFeatured,
                        ':st' => $status, ':id' => $id,
                    ]);
                } catch (\Throwable $e) {
                    $stmt = $pdo->prepare('UPDATE plans SET name=:n, description=:d, cpu=:c, ram=:r, storage=:s, price_monthly=:p, specs_json=:j, status=:st WHERE id=:id');
                    $stmt->execute([
                        ':n' => $nome, ':d' => $desc !== '' ? $desc : null,
                        ':c' => $cpu, ':r' => $ram, ':s' => $storage, ':p' => $preco,
                        ':j' => $specs !== '' ? $specs : null,
                        ':st' => $status, ':id' => $id,
                    ]);
                }
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO plans (name, description, cpu, ram, storage, price_monthly, stripe_price_id, specs_json, support_channels, backup_slots, is_featured, status, created_at) VALUES (:n,:d,:c,:r,:s,:p,:sp,:j,:sc,:bs,:ft,:st,:cr)');
                    $stmt->execute([
                        ':n' => $nome, ':d' => $desc !== '' ? $desc : null,
                        ':c' => $cpu, ':r' => $ram, ':s' => $storage, ':p' => $preco,
                        ':sp' => $stripePriceId !== '' ? $stripePriceId : null,
                        ':j' => $specs !== '' ? $specs : null,
                        ':sc' => $supportChannels !== '' ? $supportChannels : null,
                        ':bs' => $backupSlots, ':ft' => $isFeatured,
                        ':st' => $status, ':cr' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $e) {
                    $stmt = $pdo->prepare('INSERT INTO plans (name, description, cpu, ram, storage, price_monthly, specs_json, status, created_at) VALUES (:n,:d,:c,:r,:s,:p,:j,:st,:cr)');
                    $stmt->execute([
                        ':n' => $nome, ':d' => $desc !== '' ? $desc : null,
                        ':c' => $cpu, ':r' => $ram, ':s' => $storage, ':p' => $preco,
                        ':j' => $specs !== '' ? $specs : null,
                        ':st' => $status, ':cr' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return $this->renderizarErro($id, $nome, $desc, $cpu, $ram, $storage, $preco, $specs, $supportChannels, $status, 'Não foi possível salvar o plano.');
        }

        $auditId = $id;
        if ($auditId <= 0) {
            try {
                $auditId = (int) $pdo->lastInsertId();
            } catch (\Throwable $e) {
                $auditId = 0;
            }
        }

        // Salvar client_id (plano exclusivo)
        if ($auditId > 0) {
            try {
                $pdo->prepare('UPDATE plans SET client_id = :cid WHERE id = :id')
                    ->execute([':cid' => $clientIdVal, ':id' => $auditId]);
            } catch (\Throwable) {}
        }

        // Auto-criar Stripe Price ID se Stripe está configurado e não tem price_id
        if ($auditId > 0 && $stripePriceId === '') {
            $stripeKey = \LRV\Core\ConfiguracoesSistema::stripeSecretKey();
            if ($stripeKey !== '') {
                try {
                    $taxaUsd = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd();
                    $precoUsd = round((float)$preco / $taxaUsd, 2);
                    $centavos = (int)round($precoUsd * 100);
                    if ($centavos > 0) {
                        $http = new \LRV\App\Services\Http\ClienteHttp();
                        // Criar produto
                        $prodResp = $http->requestForm('POST', 'https://api.stripe.com/v1/products', [
                            'Authorization' => 'Bearer ' . $stripeKey,
                        ], [
                            'name' => $nome,
                            'description' => $desc !== '' ? $desc : $nome,
                        ]);
                        $prodId = (string)($prodResp['id'] ?? '');
                        if ($prodId !== '') {
                            // Criar preço recorrente
                            $priceResp = $http->requestForm('POST', 'https://api.stripe.com/v1/prices', [
                                'Authorization' => 'Bearer ' . $stripeKey,
                            ], [
                                'product' => $prodId,
                                'unit_amount' => $centavos,
                                'currency' => 'usd',
                                'recurring[interval]' => 'month',
                            ]);
                            $newPriceId = (string)($priceResp['id'] ?? '');
                            if ($newPriceId !== '') {
                                $pdo->prepare('UPDATE plans SET stripe_price_id = :sp WHERE id = :id')
                                    ->execute([':sp' => $newPriceId, ':id' => $auditId]);
                            }
                        }
                    }
                } catch (\Throwable) {
                    // Silencioso — Stripe pode não estar configurado
                }
            }
        }

        // Salvar addons
        if ($auditId > 0) {
            $addonsRaw = [];
            $addonNames = (array)($req->post['addon_name'] ?? []);
            $addonDescs = (array)($req->post['addon_desc'] ?? []);
            $addonPrices = (array)($req->post['addon_price'] ?? []);
            foreach ($addonNames as $i => $an) {
                $addonsRaw[] = [
                    'name'        => $an,
                    'description' => $addonDescs[$i] ?? '',
                    'price'       => $addonPrices[$i] ?? 0,
                ];
            }
            $this->salvarAddons($pdo, $auditId, $addonsRaw);
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            $id > 0 ? 'plan.update' : 'plan.create',
            'plan',
            $auditId > 0 ? $auditId : null,
            [
                'plan_id' => $auditId > 0 ? $auditId : null,
                'name' => $nome,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'price_monthly' => $preco,
                'status' => $status,
                'specs_json_set' => $specs !== '',
                'specs_json_len' => $specs !== '' ? strlen($specs) : 0,
            ],
            $req,
        );

        return Resposta::redirecionar('/equipe/planos');
    }

    private function buscarAddons(\PDO $pdo, int $planId): array
    {
        try {
            $stmt = $pdo->prepare('SELECT * FROM plan_addons WHERE plan_id = :pid ORDER BY sort_order ASC, id ASC');
            $stmt->execute([':pid' => $planId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function salvarAddons(\PDO $pdo, int $planId, array $addonsRaw): void
    {
        try {
            $pdo->prepare('DELETE FROM plan_addons WHERE plan_id = :pid')->execute([':pid' => $planId]);
            $ins = $pdo->prepare('INSERT INTO plan_addons (plan_id, name, description, price, sort_order, active) VALUES (:pid,:n,:d,:p,:s,1)');
            foreach ($addonsRaw as $i => $a) {
                $name = trim((string)($a['name'] ?? ''));
                if ($name === '') continue;
                $ins->execute([
                    ':pid' => $planId,
                    ':n'   => $name,
                    ':d'   => trim((string)($a['description'] ?? '')) ?: null,
                    ':p'   => (float)($a['price'] ?? 0),
                    ':s'   => $i,
                ]);
            }
        } catch (\Throwable) {}
    }

    private function renderizarErro(int $id, string $nome, string $desc, int $cpu, int $ram, int $storage, string $preco, string $specs, string $supportChannels, string $status, string $erro): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => $erro,
            'plano' => [
                'id' => $id > 0 ? $id : null,
                'name' => $nome,
                'description' => $desc,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'price_monthly' => $preco,
                'specs_json' => $specs,
                'support_channels' => $supportChannels,
                'status' => $status,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
