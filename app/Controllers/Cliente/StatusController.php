<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class StatusController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, `key`, name, status, last_check_at, last_ok_at, last_error, meta_json, vps_id FROM status_services WHERE scope = 'client' AND client_id = :c ORDER BY vps_id DESC, id DESC");
        $stmt->execute([':c' => $clienteId]);
        $services = $stmt->fetchAll();
        $services = is_array($services) ? $services : [];

        $serviceIds = [];
        foreach ($services as $s) {
            if (!is_array($s)) {
                continue;
            }
            $id = (int) ($s['id'] ?? 0);
            if ($id > 0) {
                $serviceIds[] = $id;
            }
        }

        $uptime24 = [];
        $bars = [];
        if (!empty($serviceIds)) {
            $in = [];
            $params = [];
            foreach ($serviceIds as $i => $sid) {
                $ph = ':s' . $i;
                $in[] = $ph;
                $params[$ph] = $sid;
            }

            $sql = 'SELECT service_id, SUM(CASE WHEN status = \'operational\' THEN 1 ELSE 0 END) AS ok_total, COUNT(*) AS total FROM status_logs WHERE checked_at >= (NOW() - INTERVAL 24 HOUR) AND service_id IN (' . implode(',', $in) . ') GROUP BY service_id';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $sid = (int) ($r['service_id'] ?? 0);
                    $ok = (int) ($r['ok_total'] ?? 0);
                    $tot = (int) ($r['total'] ?? 0);
                    $uptime24[$sid] = $tot > 0 ? (($ok / $tot) * 100.0) : null;
                }
            }

            $sql = 'SELECT service_id, status FROM status_logs WHERE checked_at >= (NOW() - INTERVAL 24 HOUR) AND service_id IN (' . implode(',', $in) . ') ORDER BY service_id ASC, checked_at DESC, id DESC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            if (is_array($rows)) {
                $limite = 24;
                foreach ($rows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $sid = (int) ($r['service_id'] ?? 0);
                    if ($sid <= 0) {
                        continue;
                    }
                    if (!isset($bars[$sid])) {
                        $bars[$sid] = [];
                    }
                    if (count($bars[$sid]) >= $limite) {
                        continue;
                    }
                    $bars[$sid][] = (string) ($r['status'] ?? 'unknown');
                }
            }

            foreach ($bars as $sid => $arr) {
                $bars[$sid] = array_reverse($arr);
            }
        }

        $sqlInc = "SELECT DISTINCT i.id, i.title, i.status, i.impact, i.scope, i.message, i.started_at, i.resolved_at, i.created_at, i.updated_at\n"
            . "FROM status_incidents i\n"
            . "LEFT JOIN status_incident_services x ON x.incident_id = i.id\n"
            . "LEFT JOIN status_services s ON s.id = x.service_id\n"
            . "WHERE i.scope = 'public' OR (i.scope = 'client' AND s.client_id = :c)\n"
            . "ORDER BY i.started_at DESC, i.id DESC\n"
            . "LIMIT 20";

        $stmt = $pdo->prepare($sqlInc);
        $stmt->execute([':c' => $clienteId]);
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

        $updates = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':i' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT incident_id, status, message, created_at FROM status_incident_updates WHERE incident_id IN (' . implode(',', $in) . ') ORDER BY incident_id ASC, created_at DESC, id DESC';
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

        $incidentServices = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [':c' => $clienteId];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':is' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT x.incident_id, s.id AS service_id, s.`key`, s.name, s.scope, s.vps_id'
                . ' FROM status_incident_services x'
                . ' INNER JOIN status_services s ON s.id = x.service_id'
                . ' WHERE x.incident_id IN (' . implode(',', $in) . ')'
                . " AND (s.scope = 'public' OR s.client_id = :c)"
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

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/status-listar.php', [
            'services' => $services,
            'uptime24' => $uptime24,
            'bars' => $bars,
            'incidents' => $incidents,
            'updates' => $updates,
            'incidentServices' => $incidentServices,
        ]);

        return Resposta::html($html);
    }
}
