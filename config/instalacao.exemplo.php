<?php

/**
 * Copie este arquivo para config/instalacao.php e preencha com suas credenciais.
 * O arquivo instalacao.php é ignorado pelo Git e NUNCA deve ser commitado.
 */

return [
    'banco' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'porta' => 3306,
        'database' => 'lrvcloud',
        'usuario' => 'lrvcloud',
        'senha' => '', // Preencha com a senha do banco
        'charset' => 'utf8mb4',
    ],
];
