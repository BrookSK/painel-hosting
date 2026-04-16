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
            // Atualizar CPF/CNPJ no Asaas se temos localmente mas pode não ter sido enviado antes
            $cpf = trim((string) ($c['cpf_cnpj'] ?? ''));
            if ($cpf !== '') {
                try {
                    $this->asaas->atualizarCliente($asaasId, ['cpfCnpj' => $cpf]);
                } catch (\Throwable) {
                    // Silencioso — pode já estar atualizado
                }
            }
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

    public function criarAssinaturaDoPlano(int $clientId, int $planId, string $billingType, array $addons = [], int $periodo = 1): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, name, price_monthly, price_monthly_usd, price_annual_upfront, currency, cpu, ram, storage FROM plans WHERE id = :id AND status = \'active\'');
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            throw new \RuntimeException('Plano não encontrado.');
        }

        // Calcular preço em BRL (Asaas só aceita BRL)
        $precoBrl = (float)($plano['price_monthly'] ?? 0);
        if ($precoBrl <= 0) {
            // Plano em USD — converter pra BRL
            $precoUsd = (float)($plano['price_monthly_usd'] ?? 0);
            if ($precoUsd > 0) {
                $taxa = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd();
                $precoBrl = round($precoUsd * $taxa, 2);
            }
        }
        $precoTotal = $precoBrl;
        foreach ($addons as $a) {
            $addonBrl = (float)($a['price'] ?? 0);
            if ($addonBrl <= 0) {
                $addonUsd = (float)($a['price_usd'] ?? 0);
                if ($addonUsd > 0) {
                    $taxa = $taxa ?? \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd();
                    $addonBrl = round($addonUsd * $taxa, 2);
                }
            }
            $precoTotal += $addonBrl;
        }
        $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;

        $customerId = $this->garantirClienteAsaas($clientId);

        $agora = date('Y-m-d H:i:s');
        $isAnual = $periodo >= 12;

        // Normalizar billing type
        $billingTypeNorm = strtoupper(trim($billingType));
        if (!in_array($billingTypeNorm, ['PIX', 'BOLETO', 'CREDIT_CARD'], true)) {
            $billingTypeNorm = 'PIX';
        }

        // Para anual: usar preço anual à vista se disponível, senão mensal * 12
        $valorCobranca = $precoTotal;
        $ciclo = 'MONTHLY';
        $dueDelta = '+1 day';
        if ($isAnual) {
            // Recalcular do zero pra anual
            $upfront = (float)($plano['price_annual_upfront'] ?? 0);
            $valorCobranca = $upfront > 0 ? $upfront : ($precoBrl * 12);
            foreach ($addons as $a) {
                $addonAnual = (float)($a['price_annual'] ?? 0);
                $addonMensal = (float)($a['price'] ?? 0);
                $valorCobranca += ($addonAnual > 0 ? $addonAnual : $addonMensal) * 12;
            }
            $ciclo = 'YEARLY';
            $dueDelta = '+1 year';
        }

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

            $due = (new DateTimeImmutable('now'))->modify($dueDelta)->format('Y-m-d');

            $respAss = $this->asaas->criarAssinatura([
                'customer' => $customerId,
                'billingType' => $billingTypeNorm,
                'value' => $isAnual ? $valorCobranca : $precoTotal,
                'cycle' => $ciclo,
                'description' => 'Assinatura ' . (string) $plano['name'] . ($isAnual ? ' (Anual)' : '') . (!empty($addons) ? ' + addons' : ''),
                'nextDueDate' => $due,
            ]);

            $asaasSubId = (string) ($respAss['id'] ?? '');
            if ($asaasSubId === '') {
                throw new \RuntimeException('Asaas não retornou o id da assinatura.');
            }

            try {
                $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, asaas_subscription_id, billing_type, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, :a, :bt, :s, :n, :cr)');
                $insSub->execute([
                    ':c' => $clientId,
                    ':v' => $vpsId,
                    ':p' => (int) $plano['id'],
                    ':aj' => $addonsJson,
                    ':a' => $asaasSubId,
                    ':bt' => $billingTypeNorm,
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
