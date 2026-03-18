<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ServidoresController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status FROM servers ORDER BY id DESC');
        $servidores = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidores-listar.php', [
            'servidores' => is_array($servidores) ? $servidores : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => '',
            'servidor' => [
                'id' => null,
                'hostname' => '',
                'ip_address' => '',
                'ssh_port' => 22,
                'ram_total' => 64 * 1024,
                'cpu_total' => 16,
                'storage_total' => 1000 * 1024,
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Servidor inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $servidor = $stmt->fetch();

        if (!is_array($servidor)) {
            return Resposta::texto('Servidor não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => '',
            'servidor' => $servidor,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $hostname = trim((string) ($req->post['hostname'] ?? ''));
        $ip = trim((string) ($req->post['ip_address'] ?? ''));
        $sshPort = (int) ($req->post['ssh_port'] ?? 0);
        $ramTotal = (int) ($req->post['ram_total'] ?? 0);
        $cpuTotal = (int) ($req->post['cpu_total'] ?? 0);
        $storageTotal = (int) ($req->post['storage_total'] ?? 0);
        $status = (string) ($req->post['status'] ?? 'active');

        if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) {
            $status = 'active';
        }

        if ($hostname === '' || $ip === '' || $sshPort <= 0 || $sshPort > 65535 || $ramTotal <= 0 || $cpuTotal <= 0 || $storageTotal <= 0) {
            return $this->renderizarErro($id, $hostname, $ip, $sshPort, $ramTotal, $cpuTotal, $storageTotal, $status, 'Preencha os campos obrigatórios.');
        }

        $pdo = BancoDeDados::pdo();

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE servers SET hostname=:h, ip_address=:ip, ssh_port=:sp, ram_total=:rt, cpu_total=:ct, storage_total=:st, status=:s WHERE id=:id');
                $stmt->execute([
                    ':h' => $hostname,
                    ':ip' => $ip,
                    ':sp' => $sshPort,
                    ':rt' => $ramTotal,
                    ':ct' => $cpuTotal,
                    ':st' => $storageTotal,
                    ':s' => $status,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO servers (hostname, ip_address, ssh_port, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, created_at) VALUES (:h,:ip,:sp,:rt,0,:ct,0,:st,0,:s,:cr)');
                $stmt->execute([
                    ':h' => $hostname,
                    ':ip' => $ip,
                    ':sp' => $sshPort,
                    ':rt' => $ramTotal,
                    ':ct' => $cpuTotal,
                    ':st' => $storageTotal,
                    ':s' => $status,
                    ':cr' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            return $this->renderizarErro($id, $hostname, $ip, $sshPort, $ramTotal, $cpuTotal, $storageTotal, $status, 'Não foi possível salvar o servidor.');
        }

        return Resposta::redirecionar('/equipe/servidores');
    }

    private function renderizarErro(int $id, string $hostname, string $ip, int $sshPort, int $ramTotal, int $cpuTotal, int $storageTotal, string $status, string $erro): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => $erro,
            'servidor' => [
                'id' => $id > 0 ? $id : null,
                'hostname' => $hostname,
                'ip_address' => $ip,
                'ssh_port' => $sshPort,
                'ram_total' => $ramTotal,
                'cpu_total' => $cpuTotal,
                'storage_total' => $storageTotal,
                'status' => $status,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
