<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class StatusController
{
    public function publico(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query("SELECT id, `key`, name, description, status, last_check_at, last_ok_at, last_error, meta_json FROM status_services WHERE scope = 'public' ORDER BY name ASC");
        $services = $stmt->fetchAll();
        $services = is_array($services) ? $services : [];

        foreach ($services as $i => $s) {
            if (!is_array($s)) {
                unset($services[$i]);
                continue;
            }
            $meta = json_decode((string) ($s['meta_json'] ?? ''), true);
            $services[$i]['meta'] = is_array($meta) ? $meta : null;
        }
        $services = array_values($services);

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

        $incidentServices = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':is' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT x.incident_id, s.id AS service_id, s.`key`, s.name'
                . ' FROM status_incident_services x'
                . ' INNER JOIN status_services s ON s.id = x.service_id'
                . " WHERE s.scope = 'public' AND x.incident_id IN (" . implode(',', $in) . ')'
                . ' ORDER BY x.incident_id ASC, s.id ASC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            $rows = is_array($rows) ? $rows : [];
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

        $updates = [];
        if (!empty($incidentIds)) {
            $in = [];
            $params = [];
            foreach ($incidentIds as $i => $iid) {
                $ph = ':i' . $i;
                $in[] = $ph;
                $params[$ph] = $iid;
            }

            $sql = 'SELECT id, incident_id, status, message, created_at FROM status_incident_updates WHERE incident_id IN (' . implode(',', $in) . ') ORDER BY created_at DESC, id DESC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            $rows = is_array($rows) ? $rows : [];

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

        return Resposta::json([
            'ok' => true,
            'services' => $services,
            'incidents' => $incidents,
            'updates' => $updates,
            'incident_services' => $incidentServices,
        ]);
    }

    public function cliente(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'unauthorized'], 401);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, `key`, name, status, last_check_at, last_ok_at, last_error, meta_json, vps_id FROM status_services WHERE scope = 'client' AND client_id = :c ORDER BY vps_id DESC, id DESC");
        $stmt->execute([':c' => $clienteId]);
        $services = $stmt->fetchAll();
        $services = is_array($services) ? $services : [];

        foreach ($services as $i => $s) {
            if (!is_array($s)) {
                unset($services[$i]);
                continue;
            }
            $meta = json_decode((string) ($s['meta_json'] ?? ''), true);
            $services[$i]['meta'] = is_array($meta) ? $meta : null;
        }
        $services = array_values($services);

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

            $sql = 'SELECT id, incident_id, status, message, created_at FROM status_incident_updates WHERE incident_id IN (' . implode(',', $in) . ') ORDER BY created_at DESC, id DESC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
            $rows = is_array($rows) ? $rows : [];

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
            $rows = is_array($rows) ? $rows : [];
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

        return Resposta::json([
            'ok' => true,
            'services' => $services,
            'incidents' => $incidents,
            'updates' => $updates,
            'incident_services' => $incidentServices,
        ]);
    }

    public function equipe(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'unauthorized'], 401);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query('SELECT id, `key`, name, scope, client_id, server_id, vps_id, status, last_check_at, last_ok_at, last_error, meta_json FROM status_services ORDER BY scope ASC, id DESC');
        $services = $stmt->fetchAll();
        $services = is_array($services) ? $services : [];

        foreach ($services as $i => $s) {
            if (!is_array($s)) {
                unset($services[$i]);
                continue;
            }
            $meta = json_decode((string) ($s['meta_json'] ?? ''), true);
            $services[$i]['meta'] = is_array($meta) ? $meta : null;
        }
        $services = array_values($services);

        $stmt = $pdo->query('SELECT id, title, status, impact, scope, message, started_at, resolved_at, created_at, updated_at FROM status_incidents ORDER BY started_at DESC, id DESC LIMIT 50');
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
            $rows = is_array($rows) ? $rows : [];
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
            $rows = is_array($rows) ? $rows : [];
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

        return Resposta::json([
            'ok' => true,
            'services' => $services,
            'incidents' => $incidents,
            'updates' => $updates,
            'incident_services' => $incidentServices,
        ]);
    }
}
