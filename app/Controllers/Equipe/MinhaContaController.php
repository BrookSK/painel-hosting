<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class MinhaContaController
{
    private const MAX_AVATAR = 2 * 1024 * 1024;
    private const TIPOS_OK   = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
    private const EXT_MAP    = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    private const UPLOAD_DIR = 'public/uploads/avatars';

    public function index(Requisicao $req): Resposta
    {
        $id  = Auth::equipeId();
        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT name, email, role, avatar_url FROM users WHERE id = :id');
        $s->execute([':id' => $id]);
        $usuario = $s->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/minha-conta.php', [
            'usuario' => $usuario,
            'ok'      => '',
            'erro'    => '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id   = Auth::equipeId();
        $pdo  = BancoDeDados::pdo();
        $nome = trim((string) ($req->post['name'] ?? ''));
        $email = trim((string) ($req->post['email'] ?? ''));
        $senhaAtual = (string) ($req->post['senha_atual'] ?? '');
        $novaSenha  = (string) ($req->post['nova_senha'] ?? '');

        if ($nome === '' || $email === '') {
            return $this->renderComErro($id, $pdo, 'Nome e e-mail são obrigatórios.');
        }

        // Verificar e-mail duplicado
        $dup = $pdo->prepare('SELECT id FROM users WHERE email = :e AND id != :id');
        $dup->execute([':e' => $email, ':id' => $id]);
        if ($dup->fetchColumn()) {
            return $this->renderComErro($id, $pdo, 'Este e-mail já está em uso.');
        }

        // Processar avatar se enviado
        $avatarUrl = null;
        $file = $_FILES['avatar'] ?? null;
        if (is_array($file) && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $result = $this->processarAvatar($file);
            if (isset($result['erro'])) {
                return $this->renderComErro($id, $pdo, $result['erro']);
            }
            $avatarUrl = $result['url'];
        }

        // Alterar senha se solicitado
        if ($novaSenha !== '') {
            if (strlen($novaSenha) < 8) {
                return $this->renderComErro($id, $pdo, 'A nova senha deve ter ao menos 8 caracteres.');
            }
            $row = $pdo->prepare('SELECT password FROM users WHERE id = :id');
            $row->execute([':id' => $id]);
            $hash = (string) $row->fetchColumn();
            if (!password_verify($senhaAtual, $hash)) {
                return $this->renderComErro($id, $pdo, 'Senha atual incorreta.');
            }
            $novoHash = password_hash($novaSenha, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE users SET password = :p WHERE id = :id')->execute([':p' => $novoHash, ':id' => $id]);
        }

        if ($avatarUrl !== null) {
            $pdo->prepare('UPDATE users SET name = :n, email = :e, avatar_url = :a WHERE id = :id')
                ->execute([':n' => $nome, ':e' => $email, ':a' => $avatarUrl, ':id' => $id]);
        } else {
            $pdo->prepare('UPDATE users SET name = :n, email = :e WHERE id = :id')
                ->execute([':n' => $nome, ':e' => $email, ':id' => $id]);
        }

        $s = $pdo->prepare('SELECT name, email, role, avatar_url FROM users WHERE id = :id');
        $s->execute([':id' => $id]);
        $usuario = $s->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/minha-conta.php', [
            'usuario' => $usuario,
            'ok'      => 'Dados atualizados com sucesso.',
            'erro'    => '',
        ]));
    }

    private function processarAvatar(array $file): array
    {
        if ((int)$file['size'] > self::MAX_AVATAR) {
            return ['erro' => 'Avatar muito grande (máx. 2 MB).'];
        }
        $tmp = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            return ['erro' => 'Arquivo inválido.'];
        }
        $mime = mime_content_type($tmp);
        if (!in_array($mime, self::TIPOS_OK, true)) {
            return ['erro' => 'Formato não permitido. Use PNG, JPG, WEBP ou GIF.'];
        }
        $ext = self::EXT_MAP[$mime] ?? 'png';
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        $dir  = rtrim($base, '/\\') . '/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $nome    = bin2hex(random_bytes(12)) . '.' . $ext;
        $destino = $dir . '/' . $nome;
        if (!move_uploaded_file($tmp, $destino)) {
            return ['erro' => 'Falha ao salvar o avatar.'];
        }
        return ['url' => '/uploads/avatars/' . $nome];
    }

    private function renderComErro(int $id, \PDO $pdo, string $erro): Resposta
    {
        $s = $pdo->prepare('SELECT name, email, role, avatar_url FROM users WHERE id = :id');
        $s->execute([':id' => $id]);
        $usuario = $s->fetch() ?: [];
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/minha-conta.php', [
            'usuario' => $usuario,
            'ok'      => '',
            'erro'    => $erro,
        ]));
    }
}
