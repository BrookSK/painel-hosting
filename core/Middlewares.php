<?php

declare(strict_types=1);

namespace LRV\Core;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class Middlewares
{
    public static function exigirLoginEquipe(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            if (Auth::equipeId() === null) {
                return Resposta::redirecionar('/equipe/entrar');
            }
            return null;
        };
    }

    public static function exigirLoginCliente(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            if (Auth::clienteId() === null) {
                return Resposta::redirecionar('/cliente/entrar');
            }
            return null;
        };
    }

    public static function exigirPermissao(string $permissao): callable
    {
        return static function (Requisicao $req) use ($permissao): ?Resposta {
            $id = Auth::equipeId();
            if ($id === null) {
                return Resposta::redirecionar('/equipe/entrar');
            }

            try {
                if (!Rbac::temPermissao($id, $permissao)) {
                    return Resposta::texto('Acesso negado.', 403);
                }
            } catch (\Throwable $e) {
                return Resposta::texto('Acesso negado.', 403);
            }

            return null;
        };
    }
}
