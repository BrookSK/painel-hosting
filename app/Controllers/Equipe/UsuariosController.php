<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Rbac;
use LRV\Core\View;

final class UsuariosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC');
        $usuarios = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/usuarios-listar.php', [
            'usuarios' => is_array($usuarios) ? $usuarios : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/usuario-editar.php', [
            'erro' => '',
            'usuario' => [
                'id' => null,
                'name' => '',
                'email' => '',
                'role' => 'admin',
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Usuário inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, role, status, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();

        if (!is_array($usuario)) {
            return Resposta::texto('Usuário não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/usuario-editar.php', [
            'erro' => '',
            'usuario' => $usuario,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $nome = trim((string) ($req->post['name'] ?? ''));
        $email = trim((string) ($req->post['email'] ?? ''));
        $role = trim((string) ($req->post['role'] ?? ''));
        $status = trim((string) ($req->post['status'] ?? ''));
        $senha = (string) ($req->post['password'] ?? '');

        $rolesValidas = ['superadmin', 'admin', 'financeiro', 'devops', 'programador', 'suporte'];
        if (!in_array($role, $rolesValidas, true)) {
            $role = 'admin';
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        if ($nome === '' || $email === '') {
            return $this->renderizarErro($id, $nome, $email, $role, $status, 'Preencha os campos obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->renderizarErro($id, $nome, $email, $role, $status, 'E-mail inválido.');
        }

        if ($id <= 0 && trim($senha) === '') {
            return $this->renderizarErro($id, $nome, $email, $role, $status, 'Informe uma senha.');
        }

        $meuId = Auth::equipeId();
        if ($meuId !== null && $id > 0 && $id === $meuId && $status !== 'active') {
            return $this->renderizarErro($id, $nome, $email, $role, 'active', 'Você não pode desativar seu próprio usuário.');
        }

        $pdo = BancoDeDados::pdo();

        try {
            if ($id > 0) {
                if (trim($senha) !== '') {
                    $hash = password_hash($senha, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare('UPDATE users SET name=:n, email=:e, role=:r, status=:s, password=:p WHERE id=:id');
                    $stmt->execute([
                        ':n' => $nome,
                        ':e' => $email,
                        ':r' => $role,
                        ':s' => $status,
                        ':p' => $hash,
                        ':id' => $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name=:n, email=:e, role=:r, status=:s WHERE id=:id');
                    $stmt->execute([
                        ':n' => $nome,
                        ':e' => $email,
                        ':r' => $role,
                        ':s' => $status,
                        ':id' => $id,
                    ]);
                }
            } else {
                $hash = password_hash($senha, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (:n,:e,:p,:r,:s,:c)');
                $stmt->execute([
                    ':n' => $nome,
                    ':e' => $email,
                    ':p' => $hash,
                    ':r' => $role,
                    ':s' => $status,
                    ':c' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            return $this->renderizarErro($id, $nome, $email, $role, $status, 'Não foi possível salvar o usuário. Verifique se o e-mail já existe.');
        }

        Rbac::limparCache();

        return Resposta::redirecionar('/equipe/usuarios');
    }

    private function renderizarErro(int $id, string $nome, string $email, string $role, string $status, string $erro): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/usuario-editar.php', [
            'erro' => $erro,
            'usuario' => [
                'id' => $id > 0 ? $id : null,
                'name' => $nome,
                'email' => $email,
                'role' => $role,
                'status' => $status,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
