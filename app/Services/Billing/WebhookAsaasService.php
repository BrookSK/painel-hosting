<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use DateInterval;
use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Jobs\RepositorioJobs;

final class WebhookAsaasService
{
    public function processar(array $evento): void
    {
        $idEvento = (string) ($evento['id'] ?? '');
        $tipo = (string) ($evento['event'] ?? '');

        if ($idEvento === '' || $tipo === '') {
            return;
        }

        $pdo = BancoDeDados::pdo();

        $pdo->beginTransaction();
        try {
            try {
                $ja = $pdo->prepare('SELECT id FROM asaas_events WHERE event_id = :i LIMIT 1');
                $ja->execute([':i' => $idEvento]);
                if ($ja->fetch()) {
                    $pdo->commit();
                    return;
                }

                $ins = $pdo->prepare('INSERT INTO asaas_events (event_id, event_type, created_at) VALUES (:i, :t, :c)');
                $ins->execute([
                    ':i' => $idEvento,
                    ':t' => $tipo,
                    ':c' => date('Y-m-d H:i:s'),
                ]);
            } catch (\PDOException $e) {
                $code = (string) $e->getCode();
                if ($code === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    $pdo->commit();
                    return;
                }
            } catch (\Throwable $e) {
            }

            $subscriptionId = '';
            $nextDueDate = null;

            $payment = $evento['payment'] ?? null;
            if (is_array($payment)) {
                $subscriptionId = (string) ($payment['subscription'] ?? '');
                if ($subscriptionId === '') {
                    $subscriptionId = (string) ($payment['subscriptionId'] ?? '');
                }

                $due = (string) ($payment['dueDate'] ?? '');
                if ($due !== '') {
                    $nextDueDate = substr($due, 0, 10);
                }
            }

            if ($subscriptionId === '') {
                $subObj = $evento['subscription'] ?? null;
                if (is_string($subObj)) {
                    $subscriptionId = $subObj;
                } elseif (is_array($subObj)) {
                    $subscriptionId = (string) ($subObj['id'] ?? '');

                    $nd = (string) ($subObj['nextDueDate'] ?? '');
                    if ($nd !== '') {
                        $nextDueDate = substr($nd, 0, 10);
                    }
                }
            }

            if ($subscriptionId === '') {
                $subscriptionId = (string) ($evento['subscriptionId'] ?? '');
            }

            if ($subscriptionId === '') {
                $pdo->commit();
                return;
            }

            $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE asaas_subscription_id = :s LIMIT 1');
            $stmt->execute([':s' => $subscriptionId]);
            $sub = $stmt->fetch();

            if (!is_array($sub)) {
                $pdo->commit();
                return;
            }

            $subId = (int) $sub['id'];
            $vpsId = (int) ($sub['vps_id'] ?? 0);
            $clienteId = (int) ($sub['client_id'] ?? 0);

            if ($tipo === 'PAYMENT_CONFIRMED' || $tipo === 'PAYMENT_RECEIVED') {
                $this->marcarAssinaturaAtiva($subId, $nextDueDate);

                if ($vpsId > 0) {
                    $this->concluirSuspensoesPendentes($pdo, $vpsId, $subId, 'Pagamento confirmado/recebido.');

                    $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status IN ('pending_payment','suspended_payment')");
                    $upVps->execute([':id' => $vpsId]);

                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Pagamento confirmado (Asaas)',
                        'mensagem' => "Pagamento confirmado/recebido.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nEvento: {$tipo}",
                    ]);
                    $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
                    $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);

                    // Notificar cliente por e-mail
                    try {
                        $cStmt = $pdo->prepare('SELECT name, email, preferred_lang FROM clients WHERE id = :id');
                        $cStmt->execute([':id' => $clienteId]);
                        $cli = $cStmt->fetch();
                        if (is_array($cli)) {
                            $lang = trim((string)($cli['preferred_lang'] ?? ''));
                            $origLang = \LRV\Core\I18n::idioma();
                            if (in_array($lang, ['pt-BR', 'en-US', 'es-ES'], true)) {
                                \LRV\Core\I18n::definirIdioma($lang);
                            }
                            $nome = (string)($cli['name'] ?? '');
                            $email = (string)($cli['email'] ?? '');
                            $appUrl = \LRV\Core\ConfiguracoesSistema::appUrlBase();
                            $corpo = '<p style="margin:0 0 12px;">' . htmlspecialchars(\LRV\Core\I18n::t('email.pag_confirmado_corpo'), ENT_QUOTES, 'UTF-8') . '</p>'
                                   . '<p style="margin:0 0 12px;">' . htmlspecialchars(\LRV\Core\I18n::t('email.vps_provisionando_corpo'), ENT_QUOTES, 'UTF-8') . '</p>';
                            $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                                \LRV\Core\I18n::t('email.pag_confirmado_assunto'),
                                $corpo,
                                \LRV\Core\I18n::t('email.ver_vps_btn'),
                                $appUrl . '/cliente/vps',
                            );
                            (new \LRV\App\Services\Email\SmtpMailer())->enviar($email, \LRV\Core\I18n::t('email.pag_confirmado_assunto'), $html, true);
                            \LRV\Core\I18n::definirIdioma($origLang);
                        }
                    } catch (\Throwable) {}
                }

                $pdo->commit();
                return;
            }

            if ($tipo === 'PAYMENT_OVERDUE') {
                $statusAnterior = strtoupper(trim((string) ($sub['status'] ?? '')));

                // Se a assinatura nunca foi paga (PENDING), expirar direto
                if ($statusAnterior === 'PENDING') {
                    $this->atualizarAssinatura($pdo, $subId, 'EXPIRED', $nextDueDate);

                    if ($vpsId > 0) {
                        $pdo->prepare("UPDATE vps SET status = 'expired' WHERE id = :id AND status = 'pending_payment'")
                            ->execute([':id' => $vpsId]);

                        $repoJobs = new RepositorioJobs();
                        $repoJobs->criar('alerta_billing', [
                            'titulo' => 'Assinatura expirada (nunca paga)',
                            'mensagem' => "Primeira cobrança venceu sem pagamento.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                        ]);
                    }

                    $pdo->commit();
                    return;
                }

                $this->marcarAssinaturaOverdue($subId, $nextDueDate);

                if ($vpsId > 0) {
                    $dias = ConfiguracoesSistema::toleranciaPagamentoDias();
                    $quando = (new DateTimeImmutable('now'))->add(new DateInterval('P' . $dias . 'D'));

                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Pagamento overdue (Asaas)',
                        'mensagem' => "Pagamento overdue.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nTolerância (dias): {$dias}\nSuspensão agendada para: " . $quando->format('Y-m-d H:i:s'),
                    ]);
                    $repoJobs->criar('suspender_vps', [
                        'vps_id' => $vpsId,
                        'assinatura_id' => $subId,
                    ], $quando);
                }

                $pdo->commit();
                return;
            }

            if ($tipo === 'SUBSCRIPTION_CANCELED' || $tipo === 'SUBSCRIPTION_DELETED' || $tipo === 'SUBSCRIPTION_INACTIVATED') {
                $this->marcarAssinaturaCancelada($subId, $nextDueDate);

                if ($vpsId > 0) {
                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Assinatura cancelada (Asaas)',
                        'mensagem' => "Assinatura cancelada.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                    ]);
                    $repoJobs->criar('suspender_vps', [
                        'vps_id' => $vpsId,
                        'assinatura_id' => $subId,
                    ]);
                }

                $pdo->commit();
                return;
            }

            if (str_starts_with($tipo, 'SUBSCRIPTION_')) {
                $subPayload = $evento['subscription'] ?? null;
                if (is_array($subPayload)) {
                    $statusAnterior = strtoupper(trim((string) ($sub['status'] ?? '')));
                    $statusPayload = strtoupper(trim((string) ($subPayload['status'] ?? '')));
                    if ($statusPayload !== '') {
                        if ($statusPayload === 'ACTIVE') {
                            $this->marcarAssinaturaAtiva($subId, $nextDueDate);

                            if ($vpsId > 0 && $statusAnterior !== 'ACTIVE') {
                                $this->concluirSuspensoesPendentes($pdo, $vpsId, $subId, 'Assinatura reativada (ACTIVE).');

                                $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status IN ('pending_payment','suspended_payment')");
                                $upVps->execute([':id' => $vpsId]);

                                $repoJobs = new RepositorioJobs();
                                $repoJobs->criar('alerta_billing', [
                                    'titulo' => 'Assinatura ativa (Asaas)',
                                    'mensagem' => "Assinatura marcada como ACTIVE via evento {$tipo}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                                ]);
                                $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
                                $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
                            }
                        } elseif (in_array($statusPayload, ['INACTIVE', 'CANCELED', 'DELETED'], true)) {
                            $this->marcarAssinaturaCancelada($subId, $nextDueDate);

                            if ($vpsId > 0 && !in_array($statusAnterior, ['INACTIVE', 'CANCELED', 'DELETED'], true)) {
                                $repoJobs = new RepositorioJobs();
                                $repoJobs->criar('alerta_billing', [
                                    'titulo' => 'Assinatura inativa/cancelada (Asaas)',
                                    'mensagem' => "Assinatura marcada como {$statusPayload} via evento {$tipo}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                                ]);
                                $repoJobs->criar('suspender_vps', [
                                    'vps_id' => $vpsId,
                                    'assinatura_id' => $subId,
                                ]);
                            }
                        } elseif ($statusPayload === 'SUSPENDED') {
                            $this->marcarAssinaturaSuspensa($subId, $nextDueDate);

                            if ($vpsId > 0 && $statusAnterior !== 'SUSPENDED') {
                                $repoJobs = new RepositorioJobs();
                                $repoJobs->criar('alerta_billing', [
                                    'titulo' => 'Assinatura suspensa (Asaas)',
                                    'mensagem' => "Assinatura marcada como SUSPENDED via evento {$tipo}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                                ]);
                                $repoJobs->criar('suspender_vps', [
                                    'vps_id' => $vpsId,
                                    'assinatura_id' => $subId,
                                ]);
                            }
                        }
                    }
                }

                $pdo->commit();
                return;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function marcarAssinaturaAtiva(int $id, ?string $nextDueDate): void
    {
        $pdo = BancoDeDados::pdo();
        $this->atualizarAssinatura($pdo, $id, 'ACTIVE', $nextDueDate);
    }

    private function marcarAssinaturaOverdue(int $id, ?string $nextDueDate): void
    {
        $pdo = BancoDeDados::pdo();
        $this->atualizarAssinatura($pdo, $id, 'OVERDUE', $nextDueDate);
    }

    private function marcarAssinaturaCancelada(int $id, ?string $nextDueDate): void
    {
        $pdo = BancoDeDados::pdo();
        $this->atualizarAssinatura($pdo, $id, 'CANCELED', $nextDueDate);
    }

    private function marcarAssinaturaSuspensa(int $id, ?string $nextDueDate): void
    {
        $pdo = BancoDeDados::pdo();
        $this->atualizarAssinatura($pdo, $id, 'SUSPENDED', $nextDueDate);
    }

    private function atualizarAssinatura(\PDO $pdo, int $id, string $status, ?string $nextDueDate): void
    {
        $params = [
            ':id' => $id,
        ];

        $setDue = false;
        if (is_string($nextDueDate)) {
            $d = trim($nextDueDate);
            if ($d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1) {
                $setDue = true;
                $params[':n'] = $d;
            }
        }

        if ($setDue) {
            $up = $pdo->prepare('UPDATE subscriptions SET status = :s, next_due_date = :n WHERE id = :id');
            $params[':s'] = $status;
            $up->execute($params);
            return;
        }

        $up = $pdo->prepare('UPDATE subscriptions SET status = :s WHERE id = :id');
        $up->execute([
            ':s' => $status,
            ':id' => $id,
        ]);
    }

    private function concluirSuspensoesPendentes(\PDO $pdo, int $vpsId, int $assinaturaId, string $motivo): void
    {
        $stmt = $pdo->prepare("SELECT id, payload FROM jobs WHERE status = 'pending' AND type = 'suspender_vps' ORDER BY id DESC LIMIT 200");
        $stmt->execute();
        $jobs = $stmt->fetchAll();

        if (!is_array($jobs) || $jobs === []) {
            return;
        }

        foreach ($jobs as $j) {
            if (!is_array($j)) {
                continue;
            }

            $payloadStr = (string) ($j['payload'] ?? '');
            $payloadArr = json_decode($payloadStr, true);
            if (!is_array($payloadArr)) {
                continue;
            }

            $jVpsId = (int) ($payloadArr['vps_id'] ?? 0);
            $jAssId = (int) ($payloadArr['assinatura_id'] ?? 0);
            if ($jVpsId !== $vpsId || $jAssId !== $assinaturaId) {
                continue;
            }

            $jobId = (int) ($j['id'] ?? 0);
            if ($jobId <= 0) {
                continue;
            }

            $msg = "\n[CANCELADO] suspender_vps encerrado automaticamente. Motivo: " . $motivo . ' - ' . date('Y-m-d H:i:s');
            $up = $pdo->prepare("UPDATE jobs SET status = 'completed', log = CONCAT(COALESCE(log,''), :m), updated_at = :u WHERE id = :id AND status = 'pending'");
            $up->execute([
                ':m' => $msg,
                ':u' => date('Y-m-d H:i:s'),
                ':id' => $jobId,
            ]);
        }
    }
}
