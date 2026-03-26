<?php
declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class MinhaContaController
{
    public function index(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $s->execute([':id' => $id]);
        $cliente = $s->fetch();
        if (!is_array($cliente)) return Resposta::redirecionar('/cliente/entrar');

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/minha-conta.php', [
            'cliente' => $cliente,
            'ok'      => (string)($req->query['ok'] ?? ''),
            'erro'    => '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) return Resposta::redirecionar('/cliente/entrar');

        $aba = (string)($req->post['aba'] ?? 'dados');

        if ($aba === 'senha') {
            return $this->salvarSenha($id, $req);
        }

        return $this->salvarDados($id, $req);
    }

    private function salvarDados(int $id, Requisicao $req): Resposta
    {
        $nome   = trim((string)($req->post['name'] ?? ''));
        $phone  = trim((string)($req->post['phone'] ?? ''));
        $mobile = trim((string)($req->post['mobile_phone'] ?? ''));
        $cpf    = trim((string)($req->post['cpf_cnpj'] ?? ''));
        $street = trim((string)($req->post['address_street'] ?? ''));
        $number = trim((string)($req->post['address_number'] ?? ''));
        $comp   = trim((string)($req->post['address_complement'] ?? ''));
        $dist   = trim((string)($req->post['address_district'] ?? ''));
        $city   = trim((string)($req->post['address_city'] ?? ''));
        $state  = strtoupper(trim((string)($req->post['address_state'] ?? '')));
        $zip    = preg_replace('/\D/', '', (string)($req->post['address_zip'] ?? ''));
        $country= strtoupper(trim((string)($req->post['address_country'] ?? 'BR')));
        $clientCountry = strtoupper(trim((string)($req->post['country'] ?? '')));
        $prefLang = trim((string)($req->post['preferred_lang'] ?? ''));

        if ($nome === '') {
            return $this->renderErro($id, 'Nome é obrigatório.');
        }

        $pdo = BancoDeDados::pdo();
        $pdo->prepare(
            'UPDATE clients SET
                name=:n, phone=:p, mobile_phone=:m, cpf_cnpj=:c,
                country=:cc, preferred_lang=:pl,
                address_street=:st, address_number=:nu, address_complement=:co,
                address_district=:di, address_city=:ci, address_state=:s,
                address_zip=:z, address_country=:ct
             WHERE id=:id'
        )->execute([
            ':n' => $nome, ':p' => $phone ?: null, ':m' => $mobile ?: null, ':c' => $cpf ?: null,
            ':cc' => $clientCountry !== '' ? substr($clientCountry, 0, 2) : null,
            ':pl' => in_array($prefLang, ['pt-BR', 'en-US', 'es-ES'], true) ? $prefLang : null,
            ':st' => $street ?: null, ':nu' => $number ?: null, ':co' => $comp ?: null,
            ':di' => $dist ?: null, ':ci' => $city ?: null, ':s' => $state ?: null,
            ':z' => $zip ?: null, ':ct' => $country ?: 'BR',
            ':id' => $id,
        ]);

        return Resposta::redirecionar('/cliente/minha-conta?ok=dados');
    }

    private function salvarSenha(int $id, Requisicao $req): Resposta
    {
        $atual  = (string)($req->post['senha_atual'] ?? '');
        $nova   = (string)($req->post['senha_nova'] ?? '');
        $conf   = (string)($req->post['senha_confirmar'] ?? '');

        if ($atual === '' || $nova === '' || $conf === '') {
            return $this->renderErro($id, 'Preencha todos os campos de senha.');
        }
        if ($nova !== $conf) {
            return $this->renderErro($id, 'A nova senha e a confirmação não coincidem.');
        }
        if (strlen($nova) < 8) {
            return $this->renderErro($id, 'A nova senha deve ter pelo menos 8 caracteres.');
        }

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT password FROM clients WHERE id = :id');
        $s->execute([':id' => $id]);
        $row = $s->fetch();

        if (!is_array($row) || !password_verify($atual, (string)($row['password'] ?? ''))) {
            return $this->renderErro($id, 'Senha atual incorreta.');
        }

        $pdo->prepare('UPDATE clients SET password = :pw WHERE id = :id')
            ->execute([':pw' => password_hash($nova, PASSWORD_BCRYPT), ':id' => $id]);

        return Resposta::redirecionar('/cliente/minha-conta?ok=senha');
    }

    private function renderErro(int $id, string $erro): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $s->execute([':id' => $id]);
        $cliente = $s->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/minha-conta.php', [
            'cliente' => $cliente,
            'ok'      => '',
            'erro'    => $erro,
        ]), 422);
    }
}
