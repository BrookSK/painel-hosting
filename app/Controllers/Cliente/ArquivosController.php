<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ArquivosController
{
    public function index(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, cpu, ram, storage, status FROM vps WHERE client_id = :c AND status IN ('running','active') ORDER BY id DESC");
        $stmt->execute([':c' => $clienteId]);
        $vpsList = $stmt->fetchAll() ?: [];

        // Se veio app_id, buscar info da aplicação para exibir no título
        $appInfo = null;
        $appId = (int)($req->query['app_id'] ?? 0);
        if ($appId > 0) {
            $aStmt = $pdo->prepare(
                'SELECT a.id, a.type, a.domain, t.name AS template_name, t.icon AS template_icon
                 FROM applications a
                 INNER JOIN vps v ON v.id = a.vps_id
                 LEFT JOIN app_templates t ON t.id = a.template_id
                 WHERE a.id = :id AND v.client_id = :c LIMIT 1'
            );
            $aStmt->execute([':id' => $appId, ':c' => $clienteId]);
            $appInfo = $aStmt->fetch() ?: null;
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/arquivos.php', [
            'vpsList' => $vpsList,
            'appInfo' => $appInfo,
        ]));
    }

    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $vpsId = (int)($req->query['vps_id'] ?? 0);
        $appId = (int)($req->query['app_id'] ?? 0);
        $direct = (int)($req->query['direct'] ?? 0);
        $path = (string)($req->query['path'] ?? '/');
        if ($path === '') $path = '/';

        $cmd = 'ls -la --time-style=long-iso ' . escapeshellarg($path) . ' 2>&1';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        if (!$result['ok']) return Resposta::json($result);

        $output = trim($result['output']);

        // Detectar erros do ls (No such file or directory, Permission denied, etc.)
        if (str_contains($output, 'No such file or directory') || str_contains($output, 'cannot access')) {
            return Resposta::json(['ok' => false, 'erro' => 'Pasta não encontrada: ' . $path]);
        }
        if (str_contains($output, 'Permission denied')) {
            return Resposta::json(['ok' => false, 'erro' => 'Sem permissão para acessar: ' . $path]);
        }

        $lines = explode("\n", $output);
        $files = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'total') || trim($line) === '') continue;
            // Ignorar linhas que não começam com permissões (d/l/- seguido de rwx)
            if (!preg_match('/^[dlcbps-][rwxsStT-]{9}/', $line)) continue;
            $parts = preg_split('/\s+/', $line, 8);
            if (count($parts) < 8) continue;
            $name = $parts[7];
            if ($name === '.' || $name === '..') continue;
            $files[] = [
                'perms' => $parts[0],
                'type' => str_starts_with($parts[0], 'd') ? 'dir' : 'file',
                'size' => (int)$parts[4],
                'date' => $parts[5] . ' ' . $parts[6],
                'name' => $name,
            ];
        }

        usort($files, function($a, $b) {
            if ($a['type'] !== $b['type']) return $a['type'] === 'dir' ? -1 : 1;
            return strcasecmp($a['name'], $b['name']);
        });

        return Resposta::json(['ok' => true, 'path' => $path, 'files' => $files]);
    }

    public function ler(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $vpsId = (int)($req->query['vps_id'] ?? 0);
        $appId = (int)($req->query['app_id'] ?? 0);
        $direct = (int)($req->query['direct'] ?? 0);
        $path = (string)($req->query['path'] ?? '');
        if ($path === '') return Resposta::json(['ok' => false, 'erro' => 'Caminho vazio.']);

        $cmd = 'head -c 102400 ' . escapeshellarg($path) . ' 2>&1';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        if (!$result['ok']) return Resposta::json($result);

        return Resposta::json(['ok' => true, 'content' => $result['output'], 'path' => $path]);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $appId = (int)($req->post['app_id'] ?? 0);
        $direct = (int)($req->post['direct'] ?? 0);
        $path = (string)($req->post['path'] ?? '');
        $content = (string)($req->post['content'] ?? '');
        if ($path === '') return Resposta::json(['ok' => false, 'erro' => 'Caminho vazio.']);

        $b64 = base64_encode($content);
        $cmd = 'echo ' . escapeshellarg($b64) . ' | base64 -d > ' . escapeshellarg($path) . ' 2>&1 && echo OK';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        return Resposta::json($result);
    }

    public function criarPasta(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $appId = (int)($req->post['app_id'] ?? 0);
        $direct = (int)($req->post['direct'] ?? 0);
        $path = (string)($req->post['path'] ?? '');
        if ($path === '') return Resposta::json(['ok' => false, 'erro' => 'Caminho vazio.']);

        $cmd = 'mkdir -p ' . escapeshellarg($path) . ' 2>&1 && echo OK';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        return Resposta::json($result);
    }

    public function deletar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $appId = (int)($req->post['app_id'] ?? 0);
        $direct = (int)($req->post['direct'] ?? 0);
        $path = (string)($req->post['path'] ?? '');
        if ($path === '' || $path === '/') return Resposta::json(['ok' => false, 'erro' => 'Não é possível deletar este caminho.']);

        $cmd = 'rm -rf ' . escapeshellarg($path) . ' 2>&1 && echo OK';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        return Resposta::json($result);
    }

    /**
     * Download de arquivo via SSH (base64 encode no servidor, decode aqui).
     */
    public function download(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $vpsId = (int)($req->query['vps_id'] ?? 0);
        $appId = (int)($req->query['app_id'] ?? 0);
        $direct = (int)($req->query['direct'] ?? 0);
        $path = (string)($req->query['path'] ?? '');
        if ($path === '') return Resposta::texto('Caminho vazio.', 400);

        // Limitar tamanho do download (50MB)
        $checkCmd = 'stat -c%s ' . escapeshellarg($path) . ' 2>/dev/null || echo 0';
        if ($appId > 0) {
            $sizeResult = $this->execInAppContainer($clienteId, $appId, $checkCmd);
        } elseif ($direct === 1) {
            $sizeResult = $this->execDirectOnServer($clienteId, $vpsId, $checkCmd);
        } else {
            $sizeResult = $this->execInContainer($clienteId, $vpsId, $checkCmd);
        }
        $fileSize = (int)trim($sizeResult['output'] ?? '0');
        if ($fileSize > 52428800) {
            return Resposta::texto('Arquivo muito grande (máx 50MB).', 400);
        }

        $cmd = 'base64 ' . escapeshellarg($path) . ' 2>&1';
        if ($appId > 0) {
            $result = $this->execInAppContainer($clienteId, $appId, $cmd);
        } elseif ($direct === 1) {
            $result = $this->execDirectOnServer($clienteId, $vpsId, $cmd);
        } else {
            $result = $this->execInContainer($clienteId, $vpsId, $cmd);
        }
        if (!($result['ok'] ?? false)) {
            return Resposta::texto($result['erro'] ?? 'Erro ao ler arquivo.', 500);
        }

        $b64 = trim($result['output'] ?? '');
        $decoded = base64_decode($b64, true);
        if ($decoded === false) {
            return Resposta::texto('Erro ao decodificar arquivo.', 500);
        }

        $filename = basename($path);
        $mime = $this->detectarMime($filename);

        // Salvar em arquivo temporário para usar Resposta::arquivo()
        $tmpFile = sys_get_temp_dir() . '/lrv_download_' . bin2hex(random_bytes(8));
        file_put_contents($tmpFile, $decoded);

        // Registrar cleanup
        register_shutdown_function(function() use ($tmpFile) { @unlink($tmpFile); });

        return Resposta::arquivo($tmpFile, $filename, $mime);
    }

    /**
     * Upload de arquivo via SSH (base64 encode aqui, decode no servidor).
     */
    public function upload(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        // Verificar se o upload foi recebido (pode falhar silenciosamente se post_max_size for excedido)
        if (empty($_FILES) && empty($_POST)) {
            return Resposta::json(['ok' => false, 'erro' => 'Upload rejeitado pelo servidor (post_max_size excedido). Limite: ' . ini_get('post_max_size')]);
        }

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $appId = (int)($req->post['app_id'] ?? 0);
        $direct = (int)($req->post['direct'] ?? 0);
        $destPath = rtrim((string)($req->post['path'] ?? '/'), '/');
        if ($destPath === '') $destPath = '/';

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['file']['error'] ?? -1;
            $errMsg = match ($errCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande.',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado.',
                default => 'Erro no upload (código ' . $errCode . ').',
            };
            return Resposta::json(['ok' => false, 'erro' => $errMsg]);
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $fileName = basename((string)($_FILES['file']['name'] ?? 'upload'));
        $fileSize = (int)($_FILES['file']['size'] ?? 0);

        // Limitar a 50MB
        if ($fileSize > 52428800) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo muito grande (máx 50MB).']);
        }

        $fullPath = $destPath . '/' . $fileName;

        // Usar SCP para enviar o arquivo diretamente (evita limite de 2MB do escapeshellarg)
        $pdo = BancoDeDados::pdo();
        $ssh = new SshExecutor();

        if ($direct === 1) {
            // Upload direto no servidor (Git Deploy)
            $stmt = $pdo->prepare("SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
            $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
            $row = $stmt->fetch();
            if (!is_array($row)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.']);

            $ip = (string)($row['ip_address'] ?? '');
            $porta = (int)($row['ssh_port'] ?? 22);
            $usuario = (string)($row['ssh_user'] ?? 'root');
            $authType = (string)($row['ssh_auth_type'] ?? 'key');

            try {
                if ($authType === 'password') {
                    $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                    $scpResult = $ssh->scpUploadComSenha($ip, $porta, $usuario, $senha, $tmpFile, $fullPath, 120);
                } else {
                    $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                    $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                    $scpResult = $ssh->scpUpload($ip, $porta, $usuario, $keyPath, $tmpFile, $fullPath, 120);
                }
            } catch (\Throwable $e) {
                return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
            }

            if (!($scpResult['ok'] ?? false)) {
                return Resposta::json(['ok' => false, 'erro' => 'Falha ao enviar: ' . substr((string)($scpResult['saida'] ?? ''), 0, 300)]);
            }
        } else {
            // Upload para container Docker (VPS ou App)
            $stmt = $appId > 0
                ? $pdo->prepare("SELECT a.container_id, t.slug, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM applications a INNER JOIN vps v ON v.id = a.vps_id INNER JOIN servers s ON s.id = v.server_id LEFT JOIN app_templates t ON t.id = a.template_id WHERE a.id = :id AND v.client_id = :c LIMIT 1")
                : $pdo->prepare("SELECT v.container_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
            $stmt->execute([':id' => $appId > 0 ? $appId : $vpsId, ':c' => $clienteId]);
            $row = $stmt->fetch();
            if (!is_array($row)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.']);

            $ip = (string)($row['ip_address'] ?? '');
            $porta = (int)($row['ssh_port'] ?? 22);
            $usuario = (string)($row['ssh_user'] ?? 'root');
            $authType = (string)($row['ssh_auth_type'] ?? 'key');

            if ($appId > 0) {
                $slug = (string)($row['slug'] ?? 'app');
                $containerName = 'app_' . $slug . '_' . $appId;
            } else {
                $containerName = 'vps_client_' . $clienteId . '_' . $vpsId;
            }

            // SCP para /tmp no host, depois docker cp para o container
            $remoteTmp = '/tmp/lrv_upload_' . bin2hex(random_bytes(8));

            try {
                if ($authType === 'password') {
                    $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                    $scpResult = $ssh->scpUploadComSenha($ip, $porta, $usuario, $senha, $tmpFile, $remoteTmp, 120);
                } else {
                    $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                    $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                    $scpResult = $ssh->scpUpload($ip, $porta, $usuario, $keyPath, $tmpFile, $remoteTmp, 120);
                }

                if (!($scpResult['ok'] ?? false)) {
                    return Resposta::json(['ok' => false, 'erro' => 'Falha no SCP: ' . substr((string)($scpResult['saida'] ?? ''), 0, 300)]);
                }

                // docker cp do host para o container
                $cpCmd = 'docker cp ' . escapeshellarg($remoteTmp) . ' ' . escapeshellarg($containerName . ':' . $fullPath)
                    . ' && rm -f ' . escapeshellarg($remoteTmp) . ' && echo OK';

                if ($authType === 'password') {
                    $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $cpCmd, 30);
                } else {
                    $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $cpCmd, 30);
                }

                $output = (string)($result['saida'] ?? '');
                if (!str_contains($output, 'OK')) {
                    return Resposta::json(['ok' => false, 'erro' => 'Falha ao copiar para container: ' . substr($output, 0, 300)]);
                }
            } catch (\Throwable $e) {
                return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
            }
        }

        return Resposta::json(['ok' => true, 'path' => $fullPath]);
    }

    private function detectarMime(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match ($ext) {
            'txt', 'log', 'md', 'csv' => 'text/plain',
            'html', 'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'gz', 'tgz' => 'application/gzip',
            'tar' => 'application/x-tar',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'sql' => 'application/sql',
            'php' => 'text/plain',
            default => 'application/octet-stream',
        };
    }

    /**
     * Executa comando com timeout customizado (para upload/download).
     */
    private function execWithTimeout(int $clienteId, int $vpsId, int $appId, int $direct, string $cmd, int $timeout): array
    {
        if ($appId > 0) {
            // App container
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                "SELECT a.container_id, a.status, t.slug,
                        s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password
                 FROM applications a
                 INNER JOIN vps v ON v.id = a.vps_id
                 INNER JOIN servers s ON s.id = v.server_id
                 LEFT JOIN app_templates t ON t.id = a.template_id
                 WHERE a.id = :id AND v.client_id = :c LIMIT 1"
            );
            $stmt->execute([':id' => $appId, ':c' => $clienteId]);
            $row = $stmt->fetch();
            if (!is_array($row)) return ['ok' => false, 'erro' => 'Aplicação não encontrada.'];
            $slug = (string)($row['slug'] ?? 'app');
            $containerName = 'app_' . $slug . '_' . $appId;
            $containerId = trim((string)($row['container_id'] ?? ''));
            $sshCmd = 'docker exec ' . escapeshellarg($containerName) . ' sh -c ' . escapeshellarg($cmd) . ' 2>&1';
            if ($containerId !== '') $sshCmd .= ' || docker exec ' . escapeshellarg($containerId) . ' sh -c ' . escapeshellarg($cmd) . ' 2>&1';
        } elseif ($direct === 1) {
            // Direct on server
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare("SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
            $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
            $row = $stmt->fetch();
            if (!is_array($row)) return ['ok' => false, 'erro' => 'VPS não encontrada.'];
            $sshCmd = $cmd;
        } else {
            // Docker container
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare("SELECT v.container_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
            $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
            $row = $stmt->fetch();
            if (!is_array($row)) return ['ok' => false, 'erro' => 'VPS não encontrada.'];
            $containerId = trim((string)($row['container_id'] ?? ''));
            $containerName = 'vps_client_' . $clienteId . '_' . $vpsId;
            $sshCmd = 'docker exec ' . escapeshellarg($containerName) . ' bash -c ' . escapeshellarg($cmd)
                . ' 2>&1 || docker exec ' . escapeshellarg($containerId) . ' bash -c ' . escapeshellarg($cmd) . ' 2>&1';
        }

        $ssh = new SshExecutor();
        $ip = (string)($row['ip_address'] ?? '');
        $porta = (int)($row['ssh_port'] ?? 22);
        $usuario = (string)($row['ssh_user'] ?? 'root');
        $authType = (string)($row['ssh_auth_type'] ?? 'key');

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $sshCmd, $timeout);
            } else {
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $sshCmd, $timeout);
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }

        $output = (string)($result['saida'] ?? '');
        $lines = explode("\n", $output);
        $clean = [];
        foreach ($lines as $l) {
            if (str_contains($l, 'Warning:') || str_contains($l, 'Permanently added') || str_contains($l, 'known_hosts')) continue;
            $clean[] = $l;
        }
        return ['ok' => true, 'output' => implode("\n", $clean)];
    }

    private function execInContainer(int $clienteId, int $vpsId, string $cmd): array
    {
        if ($vpsId <= 0) return ['ok' => false, 'erro' => 'VPS inválida.'];

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT v.id, v.container_id, v.status, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $row = $stmt->fetch();

        if (!is_array($row)) return ['ok' => false, 'erro' => 'VPS não encontrada.'];
        if (!in_array((string)($row['status'] ?? ''), ['running', 'active'], true)) return ['ok' => false, 'erro' => 'VPS não está em execução.'];

        $containerId = trim((string)($row['container_id'] ?? ''));
        if ($containerId === '') return ['ok' => false, 'erro' => 'Container não encontrado.'];

        $containerName = 'vps_client_' . $clienteId . '_' . $vpsId;
        $dockerCmd = 'docker exec ' . escapeshellarg($containerName) . ' bash -c ' . escapeshellarg($cmd)
            . ' 2>&1 || docker exec ' . escapeshellarg($containerId) . ' bash -c ' . escapeshellarg($cmd) . ' 2>&1';

        $ssh = new SshExecutor();
        $ip = (string)($row['ip_address'] ?? '');
        $porta = (int)($row['ssh_port'] ?? 22);
        $usuario = (string)($row['ssh_user'] ?? 'root');
        $authType = (string)($row['ssh_auth_type'] ?? 'key');

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $dockerCmd, 15);
            } else {
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $dockerCmd, 15);
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }

        $output = (string)($result['saida'] ?? '');
        // Filtrar warnings SSH
        $lines = explode("\n", $output);
        $clean = [];
        foreach ($lines as $l) {
            if (str_contains($l, 'Warning:') || str_contains($l, 'Permanently added') || str_contains($l, 'known_hosts')) continue;
            $clean[] = $l;
        }

        return ['ok' => true, 'output' => implode("\n", $clean)];
    }

    /**
     * Executa comando diretamente no servidor (sem docker exec).
     * Usado para Git Deploy onde os arquivos ficam no filesystem do host.
     */
    private function execDirectOnServer(int $clienteId, int $vpsId, string $cmd): array
    {
        if ($vpsId <= 0) return ['ok' => false, 'erro' => 'VPS inválida.'];

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT v.id, v.status, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v INNER JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1");
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $row = $stmt->fetch();

        if (!is_array($row)) return ['ok' => false, 'erro' => 'VPS não encontrada.'];

        $ssh = new SshExecutor();
        $ip = (string)($row['ip_address'] ?? '');
        $porta = (int)($row['ssh_port'] ?? 22);
        $usuario = (string)($row['ssh_user'] ?? 'root');
        $authType = (string)($row['ssh_auth_type'] ?? 'key');

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $cmd, 15);
            } else {
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $cmd, 15);
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }

        $output = (string)($result['saida'] ?? '');
        $lines = explode("\n", $output);
        $clean = [];
        foreach ($lines as $l) {
            if (str_contains($l, 'Warning:') || str_contains($l, 'Permanently added') || str_contains($l, 'known_hosts')) continue;
            $clean[] = $l;
        }

        return ['ok' => true, 'output' => implode("\n", $clean)];
    }

    /**
     * Executa comando dentro do container de uma aplicação (WordPress, Laravel, etc).
     */
    private function execInAppContainer(int $clienteId, int $appId, string $cmd): array
    {
        if ($appId <= 0) return ['ok' => false, 'erro' => 'Aplicação inválida.'];

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT a.id, a.container_id, a.status, a.type,
                    t.slug,
                    s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password
             FROM applications a
             INNER JOIN vps v ON v.id = a.vps_id
             INNER JOIN servers s ON s.id = v.server_id
             LEFT JOIN app_templates t ON t.id = a.template_id
             WHERE a.id = :id AND v.client_id = :c LIMIT 1"
        );
        $stmt->execute([':id' => $appId, ':c' => $clienteId]);
        $row = $stmt->fetch();

        if (!is_array($row)) return ['ok' => false, 'erro' => 'Aplicação não encontrada.'];
        if (!in_array((string)($row['status'] ?? ''), ['running', 'active'], true)) return ['ok' => false, 'erro' => 'Aplicação não está em execução.'];

        $containerId = trim((string)($row['container_id'] ?? ''));
        $slug = (string)($row['slug'] ?? 'app');
        $containerName = 'app_' . $slug . '_' . $appId;

        // Tentar sh primeiro (funciona em todos os containers, incluindo Alpine)
        $dockerCmd = 'docker exec ' . escapeshellarg($containerName) . ' sh -c ' . escapeshellarg($cmd) . ' 2>&1';
        if ($containerId !== '') {
            $dockerCmd .= ' || docker exec ' . escapeshellarg($containerId) . ' sh -c ' . escapeshellarg($cmd) . ' 2>&1';
        }

        $ssh = new SshExecutor();
        $ip = (string)($row['ip_address'] ?? '');
        $porta = (int)($row['ssh_port'] ?? 22);
        $usuario = (string)($row['ssh_user'] ?? 'root');
        $authType = (string)($row['ssh_auth_type'] ?? 'key');

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $dockerCmd, 15);
            } else {
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $dockerCmd, 15);
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => $e->getMessage()];
        }

        $output = (string)($result['saida'] ?? '');
        $lines = explode("\n", $output);
        $clean = [];
        foreach ($lines as $l) {
            if (str_contains($l, 'Warning:') || str_contains($l, 'Permanently added') || str_contains($l, 'known_hosts')) continue;
            $clean[] = $l;
        }

        return ['ok' => true, 'output' => implode("\n", $clean)];
    }
}
