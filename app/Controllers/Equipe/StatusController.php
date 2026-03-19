<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class StatusController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query('SELECT id, `key`, name, scope, client_id, server_id, vps_id, status, last_check_at, last_ok_at, last_error FROM status_services ORDER BY scope ASC, id DESC');
        $services = $stmt->fetchAll();
        $services = is_array($services) ? $services : [];

        $stmt = $pdo->query('SELECT id, title, status, impact, scope, message, started_at, resolved_at, created_by_user_id, created_at, updated_at FROM status_incidents ORDER BY started_at DESC, id DESC LIMIT 50');
        $incidents = $stmt->fetchAll();
        $incidents = is_array($incidents) ? $incidents : [];

        $incidentIds = [];
        foreach ($incidents as $inc) {
            if (!is_array($inc)) {
                continue;
            }
            $id = (int) ($inc['id'] ?? 0);
            if ($id > 0) {
                $incidentIds[] = $id;
            }
        }

        $incidentServices = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':is' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT x.incident_id, s.id AS service_id, s.`key`, s.name, s.scope, s.client_id, s.server_id, s.vps_id'
                . ' FROM status_incident_services x'
                . ' INNER JOIN status_services s ON s.id = x.service_id'
                . ' WHERE x.incident_id IN (' . implode(',', $in) . ')'
                . ' ORDER BY x.incident_id ASC, s.id ASC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $iid = (int) ($r['incident_id'] ?? 0);
                    if ($iid <= 0) {
                        continue;
                    }
                    if (!isset($incidentServices[$iid])) {
                        $incidentServices[$iid] = [];
                    }
                    $incidentServices[$iid][] = $r;
                }
            }
        }

        $updates = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':i' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT incident_id, status, message, created_at, created_by_user_id FROM status_incident_updates WHERE incident_id IN (' . implode(',', $in) . ') ORDER BY incident_id ASC, created_at DESC, id DESC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $iid = (int) ($r['incident_id'] ?? 0);
                    if ($iid <= 0) {
                        continue;
                    }
                    if (!isset($updates[$iid])) {
                        $updates[$iid] = [];
                    }
                    if (count($updates[$iid]) >= 5) {
                        continue;
                    }
                    $updates[$iid][] = $r;
                }
            }
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/status-listar.php', [
            'services' => $services,
            'incidents' => $incidents,
            'updates' => $updates,
            'incidentServices' => $incidentServices,
        ]);

        return Resposta::html($html);
    }

    public function criarIncidente(Requisicao $req): Resposta
    {
        $title = trim((string) ($req->post['title'] ?? ''));
        $impact = (string) ($req->post['impact'] ?? 'minor');
        $scope = (string) ($req->post['scope'] ?? 'public');
        $message = trim((string) ($req->post['message'] ?? ''));
        $serviceIds = $this->parseServiceIds($req->post['service_ids'] ?? null);

        if ($title === '' || $message === '') {
            return Resposta::texto('Requisição inválida.', 400);
        }

        if (!in_array($impact, ['minor', 'major', 'critical'], true)) {
            $impact = 'minor';
        }
        if (!in_array($scope, ['public', 'internal', 'client'], true)) {
            $scope = 'public';
        }

        if ($scope === 'client' && empty($serviceIds)) {
            return Resposta::texto('Incidente client precisa de serviços vinculados.', 400);
        }

        $userId = Auth::equipeId();
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        if (!empty($serviceIds)) {
            $serviceIds = $this->filtrarServiceIdsExistentes($pdo, $serviceIds);
            if ($scope === 'client' && empty($serviceIds)) {
                return Resposta::texto('Serviços inválidos.', 400);
            }
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO status_incidents (title, status, impact, scope, message, started_at, resolved_at, created_by_user_id, created_at, updated_at) VALUES (:t,'investigating',:i,:sc,:m,:st,NULL,:u,:c,:up)");
            $stmt->execute([
                ':t' => $title,
                ':i' => $impact,
                ':sc' => $scope,
                ':m' => $message,
                ':st' => $agora,
                ':u' => $userId,
                ':c' => $agora,
                ':up' => $agora,
            ]);

            $id = (int) $pdo->lastInsertId();

            $stmt2 = $pdo->prepare('INSERT INTO status_incident_updates (incident_id, status, message, created_by_user_id, created_at) VALUES (:id,:st,:m,:u,:c)');
            $stmt2->execute([
                ':id' => $id,
                ':st' => 'investigating',
                ':m' => $message,
                ':u' => $userId,
                ':c' => $agora,
            ]);

            if (!empty($serviceIds)) {
                $ins = $pdo->prepare('INSERT INTO status_incident_services (incident_id, service_id) VALUES (:iid,:sid)');
                foreach ($serviceIds as $sid) {
                    $ins->execute([':iid' => $id, ':sid' => $sid]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Não foi possível criar o incidente.', 500);
        }

        return Resposta::redirecionar('/equipe/status');
    }

    public function atualizarIncidenteServicos(Requisicao $req): Resposta
    {
        $incidentId = (int) ($req->post['incident_id'] ?? 0);
        $serviceIds = $this->parseServiceIds($req->post['service_ids'] ?? null);

        if ($incidentId <= 0) {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, scope FROM status_incidents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $incidentId]);
        $inc = $stmt->fetch();
        if (!is_array($inc)) {
            return Resposta::texto('Incidente não encontrado.', 404);
        }

        $scope = (string) ($inc['scope'] ?? 'public');
        if ($scope === 'client' && empty($serviceIds)) {
            return Resposta::texto('Incidente client precisa de serviços vinculados.', 400);
        }

        if (!empty($serviceIds)) {
            $serviceIds = $this->filtrarServiceIdsExistentes($pdo, $serviceIds);
            if ($scope === 'client' && empty($serviceIds)) {
                return Resposta::texto('Serviços inválidos.', 400);
            }
        }

        $pdo->beginTransaction();
        try {
            $del = $pdo->prepare('DELETE FROM status_incident_services WHERE incident_id = :id');
            $del->execute([':id' => $incidentId]);

            if (!empty($serviceIds)) {
                $ins = $pdo->prepare('INSERT INTO status_incident_services (incident_id, service_id) VALUES (:iid,:sid)');
                foreach ($serviceIds as $sid) {
                    $ins->execute([':iid' => $incidentId, ':sid' => $sid]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Não foi possível atualizar os serviços.', 500);
        }

        return Resposta::redirecionar('/equipe/status');
    }

    public function atualizarIncidente(Requisicao $req): Resposta
    {
        $incidentId = (int) ($req->post['incident_id'] ?? 0);
        $status = (string) ($req->post['status'] ?? 'investigating');
        $message = trim((string) ($req->post['message'] ?? ''));

        if ($incidentId <= 0 || $message === '') {
            return Resposta::texto('Requisição inválida.', 400);
        }

        if (!in_array($status, ['investigating', 'identified', 'monitoring', 'resolved'], true)) {
            $status = 'investigating';
        }

        $userId = Auth::equipeId();
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('SELECT id FROM status_incidents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $incidentId]);
        $inc = $stmt->fetch();
        if (!is_array($inc)) {
            return Resposta::texto('Incidente não encontrado.', 404);
        }

        if ($status === 'resolved') {
            $up = $pdo->prepare('UPDATE status_incidents SET status = :st, resolved_at = :r, updated_at = :u WHERE id = :id');
            $up->execute([
                ':st' => $status,
                ':r' => $agora,
                ':u' => $agora,
                ':id' => $incidentId,
            ]);
        } else {
            $up = $pdo->prepare('UPDATE status_incidents SET status = :st, updated_at = :u WHERE id = :id');
            $up->execute([
                ':st' => $status,
                ':u' => $agora,
                ':id' => $incidentId,
            ]);
        }

        $stmt2 = $pdo->prepare('INSERT INTO status_incident_updates (incident_id, status, message, created_by_user_id, created_at) VALUES (:id,:st,:m,:u,:c)');
        $stmt2->execute([
            ':id' => $incidentId,
            ':st' => $status,
            ':m' => $message,
            ':u' => $userId,
            ':c' => $agora,
        ]);

        return Resposta::redirecionar('/equipe/status');
    }

    private function parseServiceIds(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $ids = [];

        if (is_array($value)) {
            foreach ($value as $v) {
                $i = (int) $v;
                if ($i > 0) {
                    $ids[] = $i;
                }
            }
        } else {
            $s = trim((string) $value);
            if ($s !== '') {
                $parts = preg_split('/[^0-9]+/', $s);
                $parts = is_array($parts) ? $parts : [];
                foreach ($parts as $p) {
                    $i = (int) $p;
                    if ($i > 0) {
                        $ids[] = $i;
                    }
                }
            }
        }

        $ids = array_values(array_unique($ids));
        sort($ids);
        return $ids;
    }

    private function filtrarServiceIdsExistentes(\PDO $pdo, array $serviceIds): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $in = [];
        $params = [];
        foreach (array_values($serviceIds) as $i => $sid) {
            $ph = ':s' . $i;
            $in[] = $ph;
            $params[$ph] = $sid;
        }

        $sql = 'SELECT id FROM status_services WHERE id IN (' . implode(',', $in) . ')';
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();

        $out = [];
        foreach (($rows ?: []) as $r) {
            if (!is_array($r)) {
                continue;
            }
            $id = (int) ($r['id'] ?? 0);
            if ($id > 0) {
                $out[] = $id;
            }
        }

        $out = array_values(array_unique($out));
        sort($out);
        return $out;
    }
}
