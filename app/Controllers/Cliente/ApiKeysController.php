<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\PublicApi\ApiKeyService;
use LRV\Core\Auth;
use LRV\Core\Csrf;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ApiKeysController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $service = new ApiKeyService();
        $keys = $service->listarPorCliente($clienteId);

        // Se acabou de criar, a key está na sessão
        $novaChave = (string) ($_SESSION['api_key_criada'] ?? '');
        unset($_SESSION['api_key_criada']);

        $sucesso = (string) ($req->query['ok'] ?? '');
        $erro = (string) ($req->query['erro'] ?? '');

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/api-keys.php', [
            'keys' => $keys,
            'novaChave' => $novaChave,
            'sucesso' => $sucesso,
            'erro' => $erro,
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        if (!Csrf::validar((string) ($req->post['_csrf'] ?? ''))) {
            return Resposta::redirecionar('/cliente/api-keys?erro=csrf');
        }

        $nome = trim((string) ($req->post['nome'] ?? ''));
        $descricao = trim((string) ($req->post['descricao'] ?? ''));
        $ambiente = in_array($req->post['ambiente'] ?? '', ['production', 'sandbox'], true)
            ? (string) $req->post['ambiente']
            : 'production';
        $escopos = is_array($req->post['escopos'] ?? null) ? $req->post['escopos'] : [];
        $rateLimit = (int) ($req->post['rate_limit'] ?? 60);
        $expiraEm = trim((string) ($req->post['expira_em'] ?? ''));

        if ($nome === '') {
            return Resposta::redirecionar('/cliente/api-keys?erro=nome_obrigatorio');
        }

        if ($rateLimit < 1 || $rateLimit > 1000) {
            $rateLimit = 60;
        }

        // Validar escopos permitidos
        $escoposValidos = [
            'clients.read', 'clients.write',
            'hosting.read', 'hosting.write',
            'tickets.read', 'tickets.write',
            'domains.read', 'domains.write',
            'billing.read', 'billing.write',
            'backups.read', 'backups.write',
            'monitoring.read',
            'webhooks.read', 'webhooks.write',
            'applications.read', 'applications.write',
            'databases.read', 'databases.write',
            'emails.read', 'emails.write',
        ];
        $escopos = array_values(array_intersect($escopos, $escoposValidos));

        if (empty($escopos)) {
            return Resposta::redirecionar('/cliente/api-keys?erro=escopos_obrigatorio');
        }

        $service = new ApiKeyService();
        $resultado = $service->criar(
            $clienteId,
            $nome,
            $ambiente,
            $escopos,
            $descricao !== '' ? $descricao : null,
            $expiraEm !== '' ? $expiraEm : null,
            $rateLimit,
        );

        // Guardar a chave na sessão para exibir uma única vez
        $_SESSION['api_key_criada'] = $resultado['key'];

        return Resposta::redirecionar('/cliente/api-keys?ok=criada');
    }

    public function revogar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        if (!Csrf::validar((string) ($req->post['_csrf'] ?? ''))) {
            return Resposta::redirecionar('/cliente/api-keys?erro=csrf');
        }

        $keyId = (int) ($req->post['key_id'] ?? 0);
        if ($keyId <= 0) {
            return Resposta::redirecionar('/cliente/api-keys?erro=invalida');
        }

        $service = new ApiKeyService();
        $ok = $service->revogar($keyId, $clienteId);

        if (!$ok) {
            return Resposta::redirecionar('/cliente/api-keys?erro=nao_encontrada');
        }

        return Resposta::redirecionar('/cliente/api-keys?ok=revogada');
    }

    public function rotacionar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        if (!Csrf::validar((string) ($req->post['_csrf'] ?? ''))) {
            return Resposta::redirecionar('/cliente/api-keys?erro=csrf');
        }

        $keyId = (int) ($req->post['key_id'] ?? 0);
        if ($keyId <= 0) {
            return Resposta::redirecionar('/cliente/api-keys?erro=invalida');
        }

        $service = new ApiKeyService();
        $resultado = $service->rotacionar($keyId, $clienteId);

        if ($resultado === null) {
            return Resposta::redirecionar('/cliente/api-keys?erro=nao_encontrada');
        }

        // Guardar a nova chave na sessão para exibir uma única vez
        $_SESSION['api_key_criada'] = $resultado['key'];

        return Resposta::redirecionar('/cliente/api-keys?ok=rotacionada');
    }
}
