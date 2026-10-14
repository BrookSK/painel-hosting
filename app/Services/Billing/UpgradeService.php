<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Jobs\RepositorioJobs;

final class UpgradeService
{
    /**
     * Lista planos disponíveis para upgrade/downgrade.
     * Retorna apenas planos do mesmo plan_type, excluindo o atual.
     */
    public function planosDisponiveis(int $subscriptionId, int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT s.id, s.plan_id, s.vps_id, s.asaas_subscription_id, s.stripe_subscription_id,
                    p.name AS plan_name, p.plan_type, p.price_monthly, p.price_monthly_usd, p.cpu, p.ram, p.storage
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.id = :id AND s.client_id = :c AND s.status IN ('ACTIVE','active')
             LIMIT 1"
        );
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return ['ok' => false, 'erro' => 'Assinatura não encontrada ou inativa.'];
        }

        $planType = (string)($sub['plan_type'] ?? 'vps');
        $currentPlanId = (int)$sub['plan_id'];

        $plansStmt = $pdo->prepare(
            "SELECT id, name, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency,
                    max_sites, max_databases, max_cron_jobs, specs_json, is_featured
             FROM plans
             WHERE status = 'active' AND client_id IS NULL AND plan_type = :t AND id != :pid
             ORDER BY price_monthly ASC"
        );
        $plansStmt->execute([':t' => $planType, ':pid' => $currentPlanId]);
        $planos = $plansStmt->fetchAll() ?: [];

        return [
            'ok' => true,
            'subscription' => $sub,
            'planos' => $planos,
            'current_plan_id' => $currentPlanId,
        ];
    }

    /**
     * Executa o upgrade/downgrade de plano.
     * Upgrade: cobra diferença proporcional imediatamente + atualiza assinatura.
     * Downgrade: só atualiza assinatura (novo valor na próxima cobrança).
     */
    public function executar(int $subscriptionId, int $newPlanId, int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        // Buscar assinatura atual
        $stmt = $pdo->prepare(
            "SELECT s.id, s.plan_id, s.vps_id, s.asaas_subscription_id, s.stripe_subscription_id,
                    s.status, s.next_due_date, s.billing_type,
                    p.name AS old_plan_name, p.price_monthly AS old_price, p.price_monthly_usd AS old_price_usd, p.plan_type
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.id = :id AND s.client_id = :c AND s.status IN ('ACTIVE','active')
             LIMIT 1"
        );
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            throw new \RuntimeException('Assinatura não encontrada ou inativa.');
        }

        $oldPlanId = (int)$sub['plan_id'];
        $vpsId = (int)($sub['vps_id'] ?? 0);
        $planType = (string)($sub['plan_type'] ?? 'vps');
        $oldPrice = (float)$sub['old_price'];

        // Buscar novo plano
        $newStmt = $pdo->prepare(
            "SELECT id, name, cpu, ram, storage, price_monthly, price_monthly_usd, plan_type
             FROM plans WHERE id = :id AND status = 'active' AND (client_id IS NULL OR client_id = :c)"
        );
        $newStmt->execute([':id' => $newPlanId, ':c' => $clientId]);
        $newPlan = $newStmt->fetch();

        if (!is_array($newPlan)) {
            throw new \RuntimeException('Plano de destino não encontrado.');
        }

        if ((string)($newPlan['plan_type'] ?? '') !== $planType) {
            throw new \RuntimeException('Não é possível trocar para um tipo de produto diferente.');
        }

        if ($oldPlanId === $newPlanId) {
            throw new \RuntimeException('Você já está neste plano.');
        }

        $newPrice = (float)$newPlan['price_monthly'];
        $isUpgrade = $newPrice > $oldPrice;
        $type = $isUpgrade ? 'upgrade' : 'downgrade';
        $agora = date('Y-m-d H:i:s');

        // Validar downgrade: verificar se uso atual cabe no novo plano
        if (!$isUpgrade) {
            $this->validarDowngrade($clientId, $newPlan, $pdo);
        }

        // Calcular pro-rata para upgrade (dias restantes do ciclo)
        $prorationCredit = 0.0;
        $prorationCharge = 0.0;
        if ($isUpgrade) {
            $nextDue = trim((string)($sub['next_due_date'] ?? ''));
            if ($nextDue !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $nextDue)) {
                $hoje = new \DateTimeImmutable('today');
                $vencimento = new \DateTimeImmutable($nextDue);
                $diasRestantes = max(0, (int)$hoje->diff($vencimento)->days);
                $diasCiclo = 30; // ciclo mensal
                if ($diasRestantes > 0 && $diasRestantes <= $diasCiclo) {
                    $diffMensal = $newPrice - $oldPrice;
                    $prorationCharge = round($diffMensal * ($diasRestantes / $diasCiclo), 2);
                }
            }
            // Se não conseguiu calcular pro-rata, cobra a diferença mensal cheia
            if ($prorationCharge <= 0) {
                $prorationCharge = round($newPrice - $oldPrice, 2);
            }
        }

        $asaasSubIdPre = trim((string)($sub['asaas_subscription_id'] ?? ''));
        $stripeSubIdPre = trim((string)($sub['stripe_subscription_id'] ?? ''));

        // Segurança: upgrade sem forma de cobrança (ex.: plano dado pela equipe) não pode
        // ser feito pelo cliente — senão ele ganharia o plano maior de graça.
        if ($isUpgrade && $asaasSubIdPre === '' && $stripeSubIdPre === '') {
            throw new \RuntimeException('Este plano não tem cobrança automática. Para fazer upgrade, fale com o suporte.');
        }

        // Asaas: o upgrade só é aplicado DEPOIS que a diferença for paga (webhook).
        if ($isUpgrade && $asaasSubIdPre !== '' && $prorationCharge > 0) {
            // Evitar vários upgrades pendentes empilhados
            $pend = $pdo->prepare("SELECT id FROM subscription_upgrades WHERE subscription_id = :s AND status = 'pending_payment' LIMIT 1");
            $pend->execute([':s' => $subscriptionId]);
            if ($pend->fetch()) {
                throw new \RuntimeException('Já existe um upgrade aguardando pagamento nesta assinatura. Pague a cobrança pendente ou aguarde ela vencer.');
            }

            $api = new AsaasApi(new ClienteHttp());
            $customerId = (new AssinaturasService($api))->garantirClienteAsaas($clientId);
            $cobranca = $api->criarCobrancaAvulsa([
                'customer' => $customerId,
                'billingType' => 'UNDEFINED', // cliente escolhe PIX, boleto ou cartão na fatura
                'value' => $prorationCharge,
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Upgrade de plano — diferença proporcional (' . (string)($newPlan['name'] ?? '') . ')',
            ]);
            $paymentId = (string)($cobranca['id'] ?? '');
            if ($paymentId === '') {
                throw new \RuntimeException('Não foi possível gerar a cobrança do upgrade. Tente novamente.');
            }

            $pdo->prepare(
                'INSERT INTO subscription_upgrades (subscription_id, client_id, old_plan_id, new_plan_id, old_price, new_price, proration_credit, proration_charge, asaas_payment_id, type, status, created_at)
                 VALUES (:sid, :c, :op, :np, :opr, :npr, :pc, :pch, :pay, :t, :s, :cr)'
            )->execute([
                ':sid' => $subscriptionId, ':c' => $clientId,
                ':op' => $oldPlanId, ':np' => $newPlanId,
                ':opr' => $oldPrice, ':npr' => $newPrice,
                ':pc' => $prorationCharge, ':pch' => $prorationCharge, ':pay' => $paymentId,
                ':t' => $type, ':s' => 'pending_payment', ':cr' => $agora,
            ]);

            return [
                'ok' => true,
                'type' => $type,
                'pending_payment' => true,
                'old_plan' => (string)$sub['old_plan_name'],
                'new_plan' => (string)$newPlan['name'],
                'upgrade_id' => (int)$pdo->lastInsertId(),
                'proration_charge' => $prorationCharge,
                'invoice_url' => (string)($cobranca['invoiceUrl'] ?? ''),
            ];
        }

        return $this->aplicarMudancaDePlano($sub, $newPlan, $clientId, $isUpgrade, $prorationCharge, null);
    }

    /**
     * Webhook: a cobrança do upgrade foi paga → aplica o novo plano agora.
     */
    public function aplicarUpgradePago(string $asaasPaymentId): bool
    {
        $pdo = BancoDeDados::pdo();
        $st = $pdo->prepare("SELECT * FROM subscription_upgrades WHERE asaas_payment_id = :p AND status = 'pending_payment' LIMIT 1");
        $st->execute([':p' => $asaasPaymentId]);
        $up = $st->fetch();
        if (!is_array($up)) {
            return false;
        }

        // Trava contra webhook duplicado
        $lock = $pdo->prepare("UPDATE subscription_upgrades SET status = 'processing' WHERE id = :id AND status = 'pending_payment'");
        $lock->execute([':id' => (int)$up['id']]);
        if ($lock->rowCount() === 0) {
            return false;
        }

        $stmt = $pdo->prepare(
            "SELECT s.id, s.plan_id, s.vps_id, s.asaas_subscription_id, s.stripe_subscription_id,
                    p.name AS old_plan_name, p.price_monthly AS old_price, p.plan_type
             FROM subscriptions s INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.id = :id AND s.client_id = :c LIMIT 1"
        );
        $stmt->execute([':id' => (int)$up['subscription_id'], ':c' => (int)$up['client_id']]);
        $sub = $stmt->fetch();
        $np = $pdo->prepare('SELECT id, name, cpu, ram, storage, price_monthly, price_monthly_usd, plan_type FROM plans WHERE id = :id');
        $np->execute([':id' => (int)$up['new_plan_id']]);
        $newPlan = $np->fetch();
        if (!is_array($sub) || !is_array($newPlan) || (int)$sub['plan_id'] !== (int)$up['old_plan_id']) {
            $pdo->prepare("UPDATE subscription_upgrades SET status = 'failed' WHERE id = :id")->execute([':id' => (int)$up['id']]);
            return false;
        }

        // A diferença já foi paga: aplicar sem gerar nova cobrança
        $this->aplicarMudancaDePlano($sub, $newPlan, (int)$up['client_id'], true, 0.0, (int)$up['id']);
        return true;
    }

    /**
     * Aplica a troca de plano (banco, VPS, gateway e resize). Usado no downgrade, no
     * upgrade via Stripe (cobrança imediata no cartão) e no upgrade Asaas já pago.
     */
    private function aplicarMudancaDePlano(array $sub, array $newPlan, int $clientId, bool $isUpgrade, float $prorationCharge, ?int $upgradeExistenteId): array
    {
        $pdo = BancoDeDados::pdo();
        $subscriptionId = (int)$sub['id'];
        $oldPlanId = (int)$sub['plan_id'];
        $newPlanId = (int)$newPlan['id'];
        $vpsId = (int)($sub['vps_id'] ?? 0);
        $oldPrice = (float)($sub['old_price'] ?? 0);
        $newPrice = (float)($newPlan['price_monthly'] ?? 0);
        $type = $isUpgrade ? 'upgrade' : 'downgrade';
        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            // 1. Registrar upgrade
            if ($upgradeExistenteId !== null) {
                $upgradeId = $upgradeExistenteId;
            } else {
                $pdo->prepare(
                    'INSERT INTO subscription_upgrades (subscription_id, client_id, old_plan_id, new_plan_id, old_price, new_price, proration_credit, type, status, created_at)
                     VALUES (:sid, :c, :op, :np, :opr, :npr, :pc, :t, :s, :cr)'
                )->execute([
                    ':sid' => $subscriptionId, ':c' => $clientId,
                    ':op' => $oldPlanId, ':np' => $newPlanId,
                    ':opr' => $oldPrice, ':npr' => $newPrice,
                    ':pc' => $prorationCharge, ':t' => $type,
                    ':s' => 'processing', ':cr' => $agora,
                ]);
                $upgradeId = (int)$pdo->lastInsertId();
            }

            // 2. Atualizar subscription no banco
            $pdo->prepare('UPDATE subscriptions SET plan_id = :p WHERE id = :id')
                ->execute([':p' => $newPlanId, ':id' => $subscriptionId]);

            // 3. Atualizar VPS no banco (novos recursos)
            if ($vpsId > 0) {
                $pdo->prepare('UPDATE vps SET cpu = :c, ram = :r, storage = :s, plan_id = :p WHERE id = :id')
                    ->execute([
                        ':c' => (int)$newPlan['cpu'],
                        ':r' => (int)$newPlan['ram'],
                        ':s' => (int)$newPlan['storage'],
                        ':p' => $newPlanId,
                        ':id' => $vpsId,
                    ]);
            }

            // 4. Atualizar gateway + cobrar upgrade imediato
            $asaasSubId = trim((string)($sub['asaas_subscription_id'] ?? ''));
            $stripeSubId = trim((string)($sub['stripe_subscription_id'] ?? ''));

            if ($asaasSubId !== '') {
                $this->atualizarAsaas($asaasSubId, $newPlan, $isUpgrade, $prorationCharge, $clientId);
            }

            if ($stripeSubId !== '') {
                $this->atualizarStripe($stripeSubId, $newPlan, $isUpgrade);
            }

            // 5. Criar job pra resize do container
            if ($vpsId > 0) {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('resize_vps', [
                    'vps_id' => $vpsId,
                    'cpu' => (int)$newPlan['cpu'],
                    'ram' => (int)$newPlan['ram'],
                    'storage' => (int)$newPlan['storage'],
                    'upgrade_id' => $upgradeId,
                ]);
            }

            // 6. Marcar upgrade como concluído
            $pdo->prepare('UPDATE subscription_upgrades SET status = :s, completed_at = :ca WHERE id = :id')
                ->execute([':s' => 'completed', ':ca' => $agora, ':id' => $upgradeId]);

            $pdo->commit();

            return [
                'ok' => true,
                'type' => $type,
                'old_plan' => (string)$sub['old_plan_name'],
                'new_plan' => (string)$newPlan['name'],
                'upgrade_id' => $upgradeId,
                'proration_charge' => $prorationCharge,
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza assinatura no Asaas: muda o valor + gera cobrança avulsa do upgrade.
     */
    private function atualizarAsaas(string $asaasSubId, array $newPlan, bool $isUpgrade, float $prorationCharge, int $clientId): void
    {
        try {
            $api = new AsaasApi(new ClienteHttp());
            $newPrice = (float)($newPlan['price_monthly'] ?? 0);
            if ($newPrice <= 0) {
                $usd = (float)($newPlan['price_monthly_usd'] ?? 0);
                $taxa = ConfiguracoesSistema::taxaConversaoUsd();
                $newPrice = round($usd * $taxa, 2);
            }

            // Atualizar valor da assinatura (próximas cobranças)
            $api->atualizarAssinatura($asaasSubId, [
                'value' => $newPrice,
                'description' => 'Assinatura ' . (string)($newPlan['name'] ?? ''),
            ]);

            // Se é upgrade, gerar cobrança avulsa imediata da diferença proporcional
            if ($isUpgrade && $prorationCharge > 0) {
                try {
                    $svc = new AssinaturasService($api);
                    $customerId = $svc->garantirClienteAsaas($clientId);

                    $api->criarCobrancaAvulsa([
                        'customer' => $customerId,
                        'billingType' => 'PIX', // PIX pra cobrança imediata
                        'value' => $prorationCharge,
                        'dueDate' => date('Y-m-d'),
                        'description' => 'Upgrade de plano — diferença proporcional (' . (string)($newPlan['name'] ?? '') . ')',
                    ]);
                } catch (\Throwable) {
                    // Se falhar a cobrança avulsa, o upgrade já foi feito — cobrar na próxima fatura
                }
            }
        } catch (\Throwable) {
            // Silencioso — o upgrade local já foi feito
        }
    }

    /**
     * Atualiza subscription no Stripe com proration imediata.
     * O Stripe cobra a diferença automaticamente via proration.
     */
    private function atualizarStripe(string $stripeSubId, array $newPlan, bool $isUpgrade): void
    {
        try {
            $secretKey = ConfiguracoesSistema::stripeSecretKey();
            if ($secretKey === '') return;

            $stripe = new \Stripe\StripeClient($secretKey);

            // Buscar subscription atual pra pegar o item ID
            $sub = $stripe->subscriptions->retrieve($stripeSubId);
            $itemId = (string)($sub->items->data[0]->id ?? '');
            if ($itemId === '') return;

            // Criar novo price pro novo plano
            $precoUsd = (float)($newPlan['price_monthly_usd'] ?? 0);
            if ($precoUsd <= 0) {
                $taxa = ConfiguracoesSistema::taxaConversaoUsd();
                $precoUsd = round((float)($newPlan['price_monthly'] ?? 0) / $taxa, 2);
            }
            $cents = (int)round($precoUsd * 100);
            if ($cents <= 0) return;

            $product = $stripe->products->create(['name' => (string)($newPlan['name'] ?? 'Plano')]);
            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $cents,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
            ]);

            // Atualizar subscription com novo price (proration automática do Stripe)
            $stripe->subscriptions->update($stripeSubId, [
                'items' => [['id' => $itemId, 'price' => $price->id]],
                'proration_behavior' => $isUpgrade ? 'always_invoice' : 'create_prorations',
            ]);
        } catch (\Throwable) {
            // Silencioso
        }
    }

    /**
     * Valida se o uso atual do cliente cabe nos limites do novo plano (downgrade).
     * Lança exceção com mensagem clara se exceder.
     */
    private function validarDowngrade(int $clientId, array $newPlan, \PDO $pdo): void
    {
        $problemas = [];

        // Verificar sites/aplicações
        $maxSites = $newPlan['max_sites'] ?? null;
        if ($maxSites !== null) {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM applications a
                 INNER JOIN vps v ON v.id = a.vps_id
                 WHERE v.client_id = :c AND a.status NOT IN ('deleted','error')"
            );
            $stmt->execute([':c' => $clientId]);
            $atualSites = (int)$stmt->fetchColumn();
            if ($atualSites > (int)$maxSites) {
                $problemas[] = "Você tem {$atualSites} sites/apps, mas o plano permite no máximo {$maxSites}. Remova " . ($atualSites - (int)$maxSites) . " antes de fazer downgrade.";
            }
        }

        // Verificar bancos de dados
        $maxDbs = $newPlan['max_databases'] ?? null;
        if ($maxDbs !== null) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_databases WHERE client_id = :c AND status = 'active'");
            $stmt->execute([':c' => $clientId]);
            $atualDbs = (int)$stmt->fetchColumn();
            if ($atualDbs > (int)$maxDbs) {
                $problemas[] = "Você tem {$atualDbs} bancos de dados, mas o plano permite no máximo {$maxDbs}. Remova " . ($atualDbs - (int)$maxDbs) . " antes de fazer downgrade.";
            }
        }

        // Verificar cron jobs
        $maxCrons = $newPlan['max_cron_jobs'] ?? null;
        if ($maxCrons !== null) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_cron_jobs WHERE client_id = :c AND status = 'active'");
            $stmt->execute([':c' => $clientId]);
            $atualCrons = (int)$stmt->fetchColumn();
            if ($atualCrons > (int)$maxCrons) {
                $problemas[] = "Você tem {$atualCrons} cron jobs, mas o plano permite no máximo {$maxCrons}. Remova " . ($atualCrons - (int)$maxCrons) . " antes de fazer downgrade.";
            }
        }

        // Verificar storage (uso de disco da VPS)
        $newStorage = (int)($newPlan['storage'] ?? 0);
        if ($newStorage > 0) {
            $stmt = $pdo->prepare(
                "SELECT v.id, v.storage FROM vps v
                 INNER JOIN subscriptions s ON s.vps_id = v.id
                 WHERE v.client_id = :c AND v.status = 'running'
                 ORDER BY v.id DESC LIMIT 1"
            );
            $stmt->execute([':c' => $clientId]);
            $vps = $stmt->fetch();
            // Nota: não temos uso real de disco aqui (precisaria de SSH pra verificar).
            // O Docker vai limitar automaticamente, mas avisamos sobre o storage menor.
        }

        if (!empty($problemas)) {
            throw new \RuntimeException("Não é possível fazer downgrade:\n\n• " . implode("\n• ", $problemas));
        }
    }

    // ═══════════════════════════════════════════════════════════
    // Addons avulsos
    // ═══════════════════════════════════════════════════════════

    /**
     * Lista addons disponíveis para contratar avulso (que o cliente ainda não tem).
     */
    public function addonsDisponiveis(int $subscriptionId, int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT s.id, s.plan_id, s.asaas_subscription_id, s.stripe_subscription_id,
                    p.name AS plan_name, p.plan_type
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.id = :id AND s.client_id = :c AND s.status IN ('ACTIVE','active')
             LIMIT 1"
        );
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return ['ok' => false, 'erro' => 'Assinatura não encontrada.'];
        }

        $planId = (int)$sub['plan_id'];

        // Buscar addons do plano
        $addStmt = $pdo->prepare(
            'SELECT id, name, description, price, price_usd FROM plan_addons WHERE plan_id = :pid AND active = 1 ORDER BY sort_order'
        );
        $addStmt->execute([':pid' => $planId]);
        $addons = $addStmt->fetchAll() ?: [];

        // Buscar addons já contratados
        $activeStmt = $pdo->prepare(
            "SELECT addon_id FROM subscription_addon_items WHERE subscription_id = :sid AND client_id = :c AND status = 'active'"
        );
        $activeStmt->execute([':sid' => $subscriptionId, ':c' => $clientId]);
        $activeIds = array_column($activeStmt->fetchAll() ?: [], 'addon_id');

        // Marcar quais já estão ativos
        foreach ($addons as &$a) {
            $a['contratado'] = in_array((int)$a['id'], $activeIds, true);
        }
        unset($a);

        return [
            'ok' => true,
            'subscription' => $sub,
            'addons' => $addons,
            'active_addon_ids' => $activeIds,
        ];
    }

    /**
     * Contrata um addon avulso para uma assinatura existente.
     * Gera cobrança imediata e registra o addon.
     */
    public function contratarAddon(int $subscriptionId, int $addonId, int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        // Buscar assinatura
        $stmt = $pdo->prepare(
            "SELECT s.id, s.plan_id, s.asaas_subscription_id, s.stripe_subscription_id
             FROM subscriptions s
             WHERE s.id = :id AND s.client_id = :c AND s.status IN ('ACTIVE','active')
             LIMIT 1"
        );
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            throw new \RuntimeException('Assinatura não encontrada ou inativa.');
        }

        // Verificar se já contratou este addon
        $checkStmt = $pdo->prepare(
            "SELECT id FROM subscription_addon_items WHERE subscription_id = :sid AND addon_id = :aid AND client_id = :c AND status = 'active' LIMIT 1"
        );
        $checkStmt->execute([':sid' => $subscriptionId, ':aid' => $addonId, ':c' => $clientId]);
        if ($checkStmt->fetch()) {
            throw new \RuntimeException('Este serviço adicional já está ativo na sua assinatura.');
        }

        // Buscar addon
        $addStmt = $pdo->prepare('SELECT id, name, price, price_usd FROM plan_addons WHERE id = :id AND plan_id = :pid AND active = 1 LIMIT 1');
        $addStmt->execute([':id' => $addonId, ':pid' => (int)$sub['plan_id']]);
        $addon = $addStmt->fetch();

        if (!is_array($addon)) {
            throw new \RuntimeException('Serviço adicional não encontrado.');
        }

        $addonPrice = (float)($addon['price'] ?? 0);
        $addonPriceUsd = (float)($addon['price_usd'] ?? 0);
        $addonName = (string)($addon['name'] ?? '');
        $agora = date('Y-m-d H:i:s');

        $asaasSubIdPre = trim((string)($sub['asaas_subscription_id'] ?? ''));
        $stripeSubIdPre = trim((string)($sub['stripe_subscription_id'] ?? ''));
        $temCusto = $addonPrice > 0 || $addonPriceUsd > 0;

        // Segurança: serviço pago sem forma de cobrança = de graça. Não permitir.
        if ($temCusto && $asaasSubIdPre === '' && $stripeSubIdPre === '') {
            throw new \RuntimeException('Esta assinatura não tem cobrança automática. Para contratar este serviço, fale com o suporte.');
        }

        // Asaas: o serviço só é ativado DEPOIS do pagamento (webhook chama ativarAddonPago).
        if ($asaasSubIdPre !== '' && $addonPrice > 0) {
            $pend = $pdo->prepare("SELECT id FROM subscription_addon_items WHERE subscription_id = :sid AND addon_id = :aid AND client_id = :c AND status = 'pending_payment' LIMIT 1");
            $pend->execute([':sid' => $subscriptionId, ':aid' => $addonId, ':c' => $clientId]);
            if ($pend->fetch()) {
                throw new \RuntimeException('Este serviço já está aguardando pagamento. Pague a cobrança pendente para ativá-lo.');
            }

            $api = new AsaasApi(new ClienteHttp());
            $customerId = (new AssinaturasService($api))->garantirClienteAsaas($clientId);
            $cobranca = $api->criarCobrancaAvulsa([
                'customer' => $customerId,
                'billingType' => 'UNDEFINED',
                'value' => $addonPrice,
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Serviço adicional: ' . $addonName,
            ]);
            $paymentId = (string)($cobranca['id'] ?? '');
            if ($paymentId === '') {
                throw new \RuntimeException('Não foi possível gerar a cobrança do serviço. Tente novamente.');
            }

            $pdo->prepare(
                'INSERT INTO subscription_addon_items (subscription_id, client_id, addon_id, addon_name, price, price_usd, asaas_payment_id, status, created_at)
                 VALUES (:sid, :c, :aid, :an, :p, :pu, :pay, :s, :cr)'
            )->execute([
                ':sid' => $subscriptionId, ':c' => $clientId, ':aid' => $addonId,
                ':an' => $addonName, ':p' => $addonPrice, ':pu' => $addonPriceUsd > 0 ? $addonPriceUsd : null,
                ':pay' => $paymentId, ':s' => 'pending_payment', ':cr' => $agora,
            ]);

            return [
                'ok' => true,
                'pending_payment' => true,
                'addon_name' => $addonName,
                'item_id' => (int)$pdo->lastInsertId(),
                'invoice_url' => (string)($cobranca['invoiceUrl'] ?? ''),
            ];
        }

        // Registrar addon contratado
        $pdo->prepare(
            'INSERT INTO subscription_addon_items (subscription_id, client_id, addon_id, addon_name, price, price_usd, status, created_at)
             VALUES (:sid, :c, :aid, :an, :p, :pu, :s, :cr)'
        )->execute([
            ':sid' => $subscriptionId, ':c' => $clientId, ':aid' => $addonId,
            ':an' => $addonName, ':p' => $addonPrice, ':pu' => $addonPriceUsd > 0 ? $addonPriceUsd : null,
            ':s' => 'active', ':cr' => $agora,
        ]);
        $itemId = (int)$pdo->lastInsertId();

        // Gerar cobrança imediata
        $asaasSubId = trim((string)($sub['asaas_subscription_id'] ?? ''));
        $stripeSubId = trim((string)($sub['stripe_subscription_id'] ?? ''));

        // (Asaas com custo já saiu acima como "aguardando pagamento"; aqui só addons gratuitos)
        if ($asaasSubId !== '') {
            try {
                $this->atualizarValorAssinaturaAsaas($asaasSubId, $subscriptionId, $pdo);
            } catch (\Throwable) {}
        }

        if ($stripeSubId !== '' && ($addonPriceUsd > 0 || $addonPrice > 0)) {
            try {
                $secretKey = ConfiguracoesSistema::stripeSecretKey();
                if ($secretKey !== '') {
                    $stripe = new \Stripe\StripeClient($secretKey);
                    $usdCents = $addonPriceUsd > 0 ? (int)round($addonPriceUsd * 100) : (int)round(($addonPrice / ConfiguracoesSistema::taxaConversaoUsd()) * 100);
                    if ($usdCents > 0) {
                        $product = $stripe->products->create(['name' => $addonName]);
                        $price = $stripe->prices->create(['product' => $product->id, 'unit_amount' => $usdCents, 'currency' => 'usd', 'recurring' => ['interval' => 'month']]);
                        $stripe->subscriptions->update($stripeSubId, [
                            'items' => [['price' => $price->id]],
                            'proration_behavior' => 'always_invoice',
                        ]);
                    }
                }
            } catch (\Throwable) {}
        }

        // Aplicar efeito técnico do addon
        $addonSlug = '';
        try {
            $slugStmt = $pdo->prepare('SELECT slug FROM plan_addons WHERE id = :id LIMIT 1');
            $slugStmt->execute([':id' => $addonId]);
            $slugRow = $slugStmt->fetch();
            $addonSlug = (string)($slugRow['slug'] ?? '');
        } catch (\Throwable) {}

        if ($addonSlug !== '') {
            $effectSvc = new AddonEffectService();
            $effectSvc->aplicar($addonSlug, $subscriptionId, $clientId);
        }

        return ['ok' => true, 'addon_name' => $addonName, 'item_id' => $itemId];
    }

    /**
     * Webhook: a cobrança do serviço adicional foi paga → ativa e aplica o efeito agora.
     */
    public function ativarAddonPago(string $asaasPaymentId): bool
    {
        $pdo = BancoDeDados::pdo();
        $st = $pdo->prepare("SELECT id, subscription_id, client_id, addon_id FROM subscription_addon_items WHERE asaas_payment_id = :p AND status = 'pending_payment' LIMIT 1");
        $st->execute([':p' => $asaasPaymentId]);
        $item = $st->fetch();
        if (!is_array($item)) {
            return false;
        }
        $lock = $pdo->prepare("UPDATE subscription_addon_items SET status = 'active' WHERE id = :id AND status = 'pending_payment'");
        $lock->execute([':id' => (int)$item['id']]);
        if ($lock->rowCount() === 0) {
            return false;
        }

        $subId = (int)$item['subscription_id'];
        $clientId = (int)$item['client_id'];

        // Passa a cobrar o addon nas próximas mensalidades
        try {
            $s = $pdo->prepare('SELECT asaas_subscription_id FROM subscriptions WHERE id = :id');
            $s->execute([':id' => $subId]);
            $asaasSubId = trim((string)$s->fetchColumn());
            if ($asaasSubId !== '') {
                $this->atualizarValorAssinaturaAsaas($asaasSubId, $subId, $pdo);
            }
        } catch (\Throwable) {}

        try {
            $slugStmt = $pdo->prepare('SELECT slug FROM plan_addons WHERE id = :id LIMIT 1');
            $slugStmt->execute([':id' => (int)$item['addon_id']]);
            $slug = (string)($slugStmt->fetchColumn() ?: '');
            if ($slug !== '') {
                (new AddonEffectService())->aplicar($slug, $subId, $clientId);
            }
        } catch (\Throwable) {}

        return true;
    }

    /**
     * Cancela um addon contratado.
     */
    public function cancelarAddon(int $itemId, int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT id, subscription_id, addon_id, addon_name FROM subscription_addon_items WHERE id = :id AND client_id = :c AND status = 'active' LIMIT 1"
        );
        $stmt->execute([':id' => $itemId, ':c' => $clientId]);
        $item = $stmt->fetch();

        if (!is_array($item)) {
            throw new \RuntimeException('Serviço adicional não encontrado ou já cancelado.');
        }

        $pdo->prepare("UPDATE subscription_addon_items SET status = 'canceled', canceled_at = :ca WHERE id = :id")
            ->execute([':ca' => date('Y-m-d H:i:s'), ':id' => $itemId]);

        // Atualizar valor da assinatura no Asaas
        $subId = (int)$item['subscription_id'];
        $subStmt = $pdo->prepare('SELECT asaas_subscription_id FROM subscriptions WHERE id = :id LIMIT 1');
        $subStmt->execute([':id' => $subId]);
        $sub = $subStmt->fetch();
        $asaasSubId = trim((string)($sub['asaas_subscription_id'] ?? ''));
        if ($asaasSubId !== '') {
            try {
                $this->atualizarValorAssinaturaAsaas($asaasSubId, $subId, $pdo);
            } catch (\Throwable) {}
        }

        // Reverter efeito técnico do addon
        try {
            $addonId = (int)($item['addon_id'] ?? 0);
            if ($addonId > 0) {
                $slugStmt = $pdo->prepare('SELECT slug FROM plan_addons WHERE id = :id LIMIT 1');
                $slugStmt->execute([':id' => $addonId]);
                $slugRow = $slugStmt->fetch();
                $addonSlug = (string)($slugRow['slug'] ?? '');
                if ($addonSlug !== '') {
                    $effectSvc = new AddonEffectService();
                    $effectSvc->reverter($addonSlug, $subId, $clientId);
                }
            }
        } catch (\Throwable) {}

        return ['ok' => true, 'addon_name' => (string)$item['addon_name']];
    }

    /**
     * Recalcula e atualiza o valor total da assinatura no Asaas (plano + addons ativos).
     */
    private function atualizarValorAssinaturaAsaas(string $asaasSubId, int $subscriptionId, \PDO $pdo): void
    {
        // Buscar preço do plano
        $stmt = $pdo->prepare(
            'SELECT p.price_monthly FROM subscriptions s INNER JOIN plans p ON p.id = s.plan_id WHERE s.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $subscriptionId]);
        $row = $stmt->fetch();
        $planPrice = (float)($row['price_monthly'] ?? 0);

        // Somar addons ativos
        $addStmt = $pdo->prepare(
            "SELECT SUM(price) AS total FROM subscription_addon_items WHERE subscription_id = :sid AND status = 'active'"
        );
        $addStmt->execute([':sid' => $subscriptionId]);
        $addRow = $addStmt->fetch();
        $addonsTotal = (float)($addRow['total'] ?? 0);

        $totalMensal = $planPrice + $addonsTotal;

        $api = new AsaasApi(new ClienteHttp());
        $api->atualizarAssinatura($asaasSubId, ['value' => $totalMensal]);
    }
}
