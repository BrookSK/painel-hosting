<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class CriarContaController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (Auth::clienteId() !== null) {
            return Resposta::redirecionar('/cliente/painel');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
            'erro' => '',
            'nome' => '',
            'email' => '',
            'cpf_cnpj' => '',
            'phone' => '',
            'mobile_phone' => '',
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        if (Auth::clienteId() !== null) {
            return Resposta::redirecionar('/cliente/painel');
        }

        $in = $req->input();

        $nome = $in->postString('nome', 190, true);
        $email = $in->postEmail('email', 190, true);
        $senha = $in->postStringRaw('senha', 255, true);

        $cpfCnpj = $in->postString('cpf_cnpj', 20, false);
        $phone = $in->postString('phone', 20, false);
        $mobilePhone = $in->postString('mobile_phone', 20, false);

        if ($in->temErros() || $nome === '' || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
                'erro' => $in->temErros() ? $in->primeiroErro() : 'Preencha nome, e-mail e senha.',
                'nome' => $nome,
                'email' => $email,
                'cpf_cnpj' => $cpfCnpj,
                'phone' => $phone,
                'mobile_phone' => $mobilePhone,
            ]);
            return Resposta::html($html, 422);
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $agora = date('Y-m-d H:i:s');

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('INSERT INTO clients (name, email, cpf_cnpj, phone, mobile_phone, password, created_at) VALUES (:n, :e, :cpf, :ph, :mph, :p, :c)');

        try {
            $stmt->execute([
                ':n' => $nome,
                ':e' => $email,
                ':cpf' => $cpfCnpj !== '' ? $cpfCnpj : null,
                ':ph' => $phone !== '' ? $phone : null,
                ':mph' => $mobilePhone !== '' ? $mobilePhone : null,
                ':p' => $hash,
                ':c' => $agora,
            ]);
        } catch (\Throwable $e) {
            try {
                $stmt2 = $pdo->prepare('INSERT INTO clients (name, email, password, created_at) VALUES (:n, :e, :p, :c)');
                $stmt2->execute([
                    ':n' => $nome,
                    ':e' => $email,
                    ':p' => $hash,
                    ':c' => $agora,
                ]);

                Auth::entrarCliente($email, $senha);
                $this->criarTrialSeAtivo($pdo, (int) $pdo->lastInsertId());
                return Resposta::redirecionar('/cliente/painel');
            } catch (\Throwable $e2) {
            }

            $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
                'erro' => 'Não foi possível criar a conta. Verifique se o e-mail já existe.',
                'nome' => $nome,
                'email' => $email,
                'cpf_cnpj' => $cpfCnpj,
                'phone' => $phone,
                'mobile_phone' => $mobilePhone,
            ]);
            return Resposta::html($html, 400);
        }

        Auth::entrarCliente($email, $senha);
        $this->criarTrialSeAtivo($pdo, (int) $pdo->lastInsertId());
        return Resposta::redirecionar('/cliente/painel');
    }

    private function criarTrialSeAtivo(\PDO $pdo, int $clientId): void
    {
        try {
            if ((int) Settings::obter('trial.enabled', 0) !== 1) {
                return;
            }
            $dias    = max(1, (int) Settings::obter('trial.dias', 7));
            $vcpu    = max(1, (int) Settings::obter('trial.vcpu', 1));
            $ramMb   = max(128, (int) Settings::obter('trial.ram_mb', 1024));
            $discoGb = max(1, (int) Settings::obter('trial.disco_gb', 20));
            $expires = date('Y-m-d H:i:s', strtotime("+{$dias} days"));

            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO client_trials (client_id, expires_at, vcpu, ram_mb, disco_gb, status)
                 VALUES (:cid, :exp, :vcpu, :ram, :disco, \'active\')'
            );
            $stmt->execute([
                ':cid'   => $clientId,
                ':exp'   => $expires,
                ':vcpu'  => $vcpu,
                ':ram'   => $ramMb,
                ':disco' => $discoGb,
            ]);
        } catch (\Throwable) {}
    }
}
