<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class StatusController
{
    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query("SELECT id, `key`, name, description, status, last_check_at, last_ok_at, last_error, meta_json FROM status_services WHERE scope = 'public' ORDER BY name ASC");
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
        }

        $bars = [];
        if (!empty($serviceIds)) {
            $in = [];
            $params = [];
            foreach ($serviceIds as $i => $sid) {
                $ph = ':b' . $i;
                $in[] = $ph;
                $params[$ph] = $sid;
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

        $stmt = $pdo->query("SELECT id, title, status, impact, scope, message, started_at, resolved_at, created_at, updated_at FROM status_incidents WHERE scope = 'public' ORDER BY started_at DESC, id DESC LIMIT 20");
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
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':is' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT x.incident_id, s.id AS service_id, s.name, s.`key`'
                . ' FROM status_incident_services x'
                . ' INNER JOIN status_services s ON s.id = x.service_id'
                . " WHERE s.scope = 'public' AND x.incident_id IN (" . implode(',', $in) . ')'
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

        $html = View::renderizar(__DIR__ . '/../Views/status.php', [
            'services' => $services,
            'uptime24' => $uptime24,
            'bars' => $bars,
            'incidents' => $incidents,
            'updates' => $updates,
            'incidentServices' => $incidentServices,
        ]);

        return Resposta::html($html);
    }

    public function incidentes(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $pagina = max(1, (int) ($req->query['pagina'] ?? 1));
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;

        $total = 0;
        $stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM status_incidents WHERE scope = 'public'");
        $rowTotal = $stmtTotal->fetch();
        if (is_array($rowTotal)) {
            $total = (int) ($rowTotal['total'] ?? 0);
        }

        $stmt = $pdo->prepare("SELECT id, title, status, impact, message, started_at, resolved_at, created_at FROM status_incidents WHERE scope = 'public' ORDER BY started_at DESC, id DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $incidents = $stmt->fetchAll();
        $incidents = is_array($incidents) ? $incidents : [];

        $incidentIds = array_filter(array_map(fn ($i) => is_array($i) ? (int) ($i['id'] ?? 0) : 0, $incidents));

        $updates = [];
        if (!empty($incidentIds)) {
            $in = implode(',', array_map('intval', $incidentIds));
            $st = $pdo->query("SELECT incident_id, status, message, created_at FROM status_incident_updates WHERE incident_id IN ({$in}) ORDER BY incident_id ASC, created_at ASC");
            $rows = $st->fetchAll();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    if (!is_array($r)) {
                        continue;
                    }
                    $iid = (int) ($r['incident_id'] ?? 0);
                    if ($iid > 0) {
                        $updates[$iid][] = $r;
                    }
                }
            }
        }

        $totalPaginas = (int) ceil($total / $porPagina);

        $html = View::renderizar(__DIR__ . '/../Views/status-incidentes.php', [
            'incidents' => $incidents,
            'updates' => $updates,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total,
        ]);

        return Resposta::html($html);
    }
}
