<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Terminal\ClientTerminalTokensService;
use LRV\App\Services\Infra\SshExecutor;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class TerminalController
{
    public function vps(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $vpsId = (int) ($req->query['id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, server_id, container_id, status, cpu, ram, storage, created_at FROM vps WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('Acesso negado.', 403);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/vps-terminal.php', [
            'vps' => $vps,
            'erro' => '',
        ]);

        return Resposta::html($html);
    }

    public function emitirToken(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $in = $req->input();
        $vpsId = $in->postInt('vps_id', 1, 2147483647, true);
        if ($in->temErros() || $vpsId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'VPS inválida.'], 400);
        }

        $ip = '';
        $xff = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
        }
        if ($ip === '') {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        }

        $ua = trim((string) ($req->headers['user-agent'] ?? ''));
        if ($ua === '') {
            $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        }

        try {
            $svc = new ClientTerminalTokensService();
            $token = $svc->criarToken($clienteId, $vpsId, $ip, $ua);

            return Resposta::json([
                'ok' => true,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if ($msg === '') {
                $msg = 'Não foi possível emitir o token.';
            }
            return Resposta::json(['ok' => false, 'erro' => $msg], 422);
        }
    }

    public function upload(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        $remotePath = trim((string) ($req->post['remote_path'] ?? ''));

        if ($vpsId <= 0 || $remotePath === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.'], 400);
        }

        if (str_contains($remotePath, '..') || preg_match('/[;&|`$]/', $remotePath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Caminho remoto inválido.'], 400);
        }

        $uploadedFile = $_FILES['file'] ?? null;
        if (!is_array($uploadedFile) || (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo não enviado.'], 400);
        }

        $size = (int) ($uploadedFile['size'] ?? 0);
        if ($size > 20 * 1024 * 1024) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo muito grande (máx. 20 MB).'], 400);
        }

        $tmpPath = (string) ($uploadedFile['tmp_name'] ?? '');
        if (!is_file($tmpPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo temporário inválido.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT v.id, v.container_id, v.status, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_key_id, s.status AS server_status
             FROM vps v
             INNER JOIN servers s ON s.id = v.server_id
             WHERE v.id = :id AND v.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.'], 403);
        }

        if ((string) ($vps['status'] ?? '') !== 'running') {
            return Resposta::json(['ok' => false, 'erro' => 'VPS não está em execução.'], 400);
        }

        $containerId = trim((string) ($vps['container_id'] ?? ''));
        if ($containerId === '' || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $containerId) !== 1) {
            return Resposta::json(['ok' => false, 'erro' => 'Contêiner inválido.'], 400);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string) ($vps['ssh_key_id'] ?? '');
        if (!is_file($keyPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Chave SSH não encontrada.'], 500);
        }

        try {
            // Upload para o node, depois copia para dentro do container via docker cp
            $scp = new SshExecutor();
            $nodeTemp = '/tmp/lrv_up_' . bin2hex(random_bytes(6));

            $result = $scp->scpUpload(
                (string) $vps['ip_address'],
                (int) ($vps['ssh_port'] ?? 22),
                (string) $vps['ssh_user'],
                $keyPath,
                $tmpPath,
                $nodeTemp,
            );

            if (!$result['ok']) {
                return Resposta::json(['ok' => false, 'erro' => 'Falha no upload para o node.'], 500);
            }

            // docker cp nodeTemp container:remotePath
            $ssh = new SshExecutor();
            $cpResult = $ssh->executar(
                (string) $vps['ip_address'],
                (int) ($vps['ssh_port'] ?? 22),
                (string) $vps['ssh_user'],
                $keyPath,
                'docker cp ' . escapeshellarg($nodeTemp) . ' ' . escapeshellarg($containerId . ':' . $remotePath) . '; rm -f ' . escapeshellarg($nodeTemp),
            );

            if (!$cpResult['ok']) {
                return Resposta::json(['ok' => false, 'erro' => 'Falha ao copiar para o contêiner.'], 500);
            }

            return Resposta::json(['ok' => true, 'mensagem' => 'Arquivo enviado para ' . $remotePath]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }
    }

    public function download(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::texto('Não autenticado.', 401);
        }

        $vpsId = (int) ($req->query['vps_id'] ?? 0);
        $remotePath = trim((string) ($req->query['remote_path'] ?? ''));

        if ($vpsId <= 0 || $remotePath === '') {
            return Resposta::texto('Parâmetros inválidos.', 400);
        }

        if (str_contains($remotePath, '..') || preg_match('/[;&|`$]/', $remotePath)) {
            return Resposta::texto('Caminho remoto inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT v.id, v.container_id, v.status, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_key_id
             FROM vps v
             INNER JOIN servers s ON s.id = v.server_id
             WHERE v.id = :id AND v.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('VPS não encontrada.', 403);
        }

        if ((string) ($vps['status'] ?? '') !== 'running') {
            return Resposta::texto('VPS não está em execução.', 400);
        }

        $containerId = trim((string) ($vps['container_id'] ?? ''));
        if ($containerId === '' || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $containerId) !== 1) {
            return Resposta::texto('Contêiner inválido.', 400);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string) ($vps['ssh_key_id'] ?? '');
        if (!is_file($keyPath)) {
            return Resposta::texto('Chave SSH não encontrada.', 500);
        }

        try {
            $ssh = new SshExecutor();
            $nodeTemp = '/tmp/lrv_dl_' . bin2hex(random_bytes(6));

            // docker cp container:remotePath nodeTemp
            $cpResult = $ssh->executar(
                (string) $vps['ip_address'],
                (int) ($vps['ssh_port'] ?? 22),
                (string) $vps['ssh_user'],
                $keyPath,
                'docker cp ' . escapeshellarg($containerId . ':' . $remotePath) . ' ' . escapeshellarg($nodeTemp),
            );

            if (!$cpResult['ok']) {
                return Resposta::texto('Falha ao copiar do contêiner: ' . ($cpResult['saida'] ?? ''), 500);
            }

            // SCP do node para local
            $scp = new SshExecutor();
            $result = $scp->scpDownload(
                (string) $vps['ip_address'],
                (int) ($vps['ssh_port'] ?? 22),
                (string) $vps['ssh_user'],
                $keyPath,
                $nodeTemp,
            );

            // Limpar temp no node
            $ssh->executar(
                (string) $vps['ip_address'],
                (int) ($vps['ssh_port'] ?? 22),
                (string) $vps['ssh_user'],
                $keyPath,
                'rm -f ' . escapeshellarg($nodeTemp),
            );

            if (!$result['ok']) {
                return Resposta::texto('Falha no download do node.', 500);
            }

            $localPath = (string) ($result['local_path'] ?? '');
            if (!is_file($localPath)) {
                return Resposta::texto('Arquivo não encontrado após download.', 500);
            }

            $filename = basename($remotePath);
            register_shutdown_function(static function() use ($localPath): void { @unlink($localPath); });

            return Resposta::arquivo($localPath, $filename);
        } catch (\Throwable $e) {
            return Resposta::texto($e->getMessage(), 500);
        }
    }
}
