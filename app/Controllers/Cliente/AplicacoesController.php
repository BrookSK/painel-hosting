<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AplicacoesController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = :id LIMIT 1');
        $cStmt->execute([':id' => $clienteId]);
        $cliente = $cStmt->fetch() ?: ['name' => 'Cliente', 'email' => ''];

        $stmt = $pdo->prepare(
            'SELECT a.id, a.vps_id, a.type, a.domain, a.port, a.status, a.repository,
                    a.template_id, a.container_id, a.created_at,
                    t.name AS template_name, t.icon AS template_icon,
                    cd.id AS db_id
             FROM applications a
             INNER JOIN vps v ON v.id = a.vps_id
             LEFT JOIN app_templates t ON t.id = a.template_id
             LEFT JOIN client_databases cd ON cd.application_id = a.id
             WHERE v.client_id = :c
             ORDER BY a.id DESC'
        );
        $stmt->execute([':c' => $clienteId]);
        $aplicacoes = $stmt->fetchAll() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/aplicacoes-listar.php',
            ['aplicacoes' => $aplicacoes, 'cliente' => $cliente]
        ));
    }

    public function catalogo(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $templates = $pdo->query('SELECT * FROM app_templates ORDER BY category, name')->fetchAll() ?: [];

        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram, storage, status FROM vps WHERE client_id = :c AND status IN ('active','running') ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = :id LIMIT 1');
        $cStmt->execute([':id' => $clienteId]);
        $cliente = $cStmt->fetch() ?: ['name' => 'Cliente', 'email' => ''];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/aplicacoes-catalogo.php',
            [
                'templates' => $templates,
                'vpsList' => $vpsList,
                'cliente' => $cliente,
                'subdomains_disponiveis' => (new \LRV\App\Services\Infra\SubdomainVerificationService())->listarAtivosDisponiveis($clienteId),
            ]
        ));
    }

    public function instalar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $templateId = (int) ($req->post['template_id'] ?? 0);
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        $domain = trim((string) ($req->post['domain'] ?? ''));
        $repository = trim((string) ($req->post['repository'] ?? ''));
        $envJson = trim((string) ($req->post['env_json'] ?? ''));

        if ($templateId <= 0 || $vpsId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
        }

        $pdo = BancoDeDados::pdo();

        // Validar template
        $tStmt = $pdo->prepare('SELECT * FROM app_templates WHERE id = :id LIMIT 1');
        $tStmt->execute([':id' => $templateId]);
        $tpl = $tStmt->fetch();
        if (!is_array($tpl)) {
            return Resposta::json(['ok' => false, 'erro' => 'Template não encontrado.'], 404);
        }

        // Validar VPS pertence ao cliente
        $vStmt = $pdo->prepare("SELECT id FROM vps WHERE id = :v AND client_id = :c AND status IN ('active','running') LIMIT 1");
        $vStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        if (!$vStmt->fetch()) {
            return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada ou inativa.'], 403);
        }

        // Validações
        if ((int) ($tpl['requires_domain'] ?? 0) === 1 && $domain === '') {
            // Verificar se pediu domínio temporário
            $gerarTempDomain = (int)($req->post['gerar_temp_domain'] ?? 0) === 1;
            if ($gerarTempDomain) {
                $tempBase = trim((string)\LRV\Core\Settings::obter('infra.temp_domain_base', ''));
                if ($tempBase !== '') {
                    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', (string)($tpl['slug'] ?? 'app')));
                    $domain = $slug . substr(bin2hex(random_bytes(3)), 0, 4) . '.' . $tempBase;
                }
            }
            if ($domain === '') {
                return Resposta::json(['ok' => false, 'erro' => 'Domínio obrigatório para esta aplicação.'], 422);
            }
        }
        if ((int) ($tpl['requires_repo'] ?? 0) === 1 && $repository === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Repositório obrigatório para esta aplicação.'], 422);
        }

        // Validar domínio
        if ($domain !== '' && !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
            return Resposta::json(['ok' => false, 'erro' => 'Domínio inválido.'], 422);
        }

        // Validar repositório
        if ($repository !== '' && !preg_match('#^https?://[a-zA-Z0-9._/-]+\.git$#i', $repository) && !preg_match('#^git@[a-zA-Z0-9._-]+:[a-zA-Z0-9._/-]+\.git$#', $repository)) {
            return Resposta::json(['ok' => false, 'erro' => 'URL do repositório inválida.'], 422);
        }

        // Validar env JSON
        if ($envJson !== '') {
            $envParsed = json_decode($envJson, true);
            if (!is_array($envParsed)) {
                return Resposta::json(['ok' => false, 'erro' => 'Variáveis de ambiente inválidas.'], 422);
            }
        }

        // Auto-assign porta
        $port = (int) ($tpl['default_port'] ?? 80);
        $portCheck = $pdo->prepare('SELECT id FROM ports WHERE port = :p AND status = :s LIMIT 1');
        $portCheck->execute([':p' => $port, ':s' => 'in_use']);
        if ($portCheck->fetch()) {
            // Encontrar porta livre entre 10000-60000
            for ($p = 10000; $p <= 60000; $p++) {
                $portCheck->execute([':p' => $p, ':s' => 'in_use']);
                if (!$portCheck->fetch()) { $port = $p; break; }
            }
        }

        $agora = date('Y-m-d H:i:s');
        $slug = (string) ($tpl['slug'] ?? 'app');

        $pdo->prepare(
            'INSERT INTO applications (vps_id, template_id, type, domain, port, status, repository, environment_json, created_at)
             VALUES (:v, :t, :tp, :d, :p, :s, :r, :e, :c)'
        )->execute([
            ':v' => $vpsId, ':t' => $templateId, ':tp' => $slug,
            ':d' => $domain !== '' ? $domain : null, ':p' => $port,
            ':s' => 'installing', ':r' => $repository !== '' ? $repository : (string) ($tpl['docker_image'] ?? ''),
            ':e' => $envJson !== '' ? $envJson : null, ':c' => $agora,
        ]);

        $appId = (int) $pdo->lastInsertId();

        // Reservar porta
        $pdo->prepare('INSERT INTO ports (port, status, application_id) VALUES (:p, :s, :a) ON DUPLICATE KEY UPDATE status = :s2, application_id = :a2')
            ->execute([':p' => $port, ':s' => 'in_use', ':a' => $appId, ':s2' => 'in_use', ':a2' => $appId]);

        // Criar job
        $repo = new \LRV\Core\Jobs\RepositorioJobs();
        $repo->criar('install_app_template', ['application_id' => $appId]);

        return Resposta::json(['ok' => true, 'application_id' => $appId, 'mensagem' => 'Instalação iniciada.']);
    }

    public function status(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $appId = (int) ($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT a.id, a.status, a.logs, a.container_id FROM applications a
             INNER JOIN vps v ON v.id = a.vps_id
             WHERE a.id = :id AND v.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $appId, ':c' => $clienteId]);
        $app = $stmt->fetch();

        if (!is_array($app)) {
            return Resposta::json(['ok' => false, 'erro' => 'Não encontrada.'], 404);
        }

        return Resposta::json(['ok' => true, 'status' => $app['status'], 'logs' => $app['logs'], 'container_id' => $app['container_id']]);
    }

    public function reinstalar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $appId = (int)($req->post['app_id'] ?? 0);
        if ($appId <= 0) return Resposta::texto('ID inválido.', 400);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT a.id FROM applications a INNER JOIN vps v ON v.id = a.vps_id WHERE a.id = :id AND v.client_id = :c LIMIT 1");
        $stmt->execute([':id' => $appId, ':c' => $clienteId]);
        if (!$stmt->fetch()) return Resposta::texto('Aplicação não encontrada.', 404);

        $pdo->prepare("UPDATE applications SET status = 'installing', logs = NULL, container_id = NULL WHERE id = :id")
            ->execute([':id' => $appId]);

        (new \LRV\Core\Jobs\RepositorioJobs())->criar('install_app_template', ['application_id' => $appId]);

        return Resposta::redirecionar('/cliente/aplicacoes');
    }

    public function deletar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $appId = (int)($req->post['app_id'] ?? 0);
        if ($appId <= 0) return Resposta::texto('ID inválido.', 400);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT a.id FROM applications a INNER JOIN vps v ON v.id = a.vps_id WHERE a.id = :id AND v.client_id = :c LIMIT 1");
        $stmt->execute([':id' => $appId, ':c' => $clienteId]);
        if (!$stmt->fetch()) return Resposta::texto('Aplicação não encontrada.', 404);

        // Liberar porta
        $pdo->prepare("DELETE FROM ports WHERE application_id = :id")->execute([':id' => $appId]);
        // Deletar aplicação
        $pdo->prepare("DELETE FROM applications WHERE id = :id")->execute([':id' => $appId]);

        return Resposta::redirecionar('/cliente/aplicacoes');
    }
}
