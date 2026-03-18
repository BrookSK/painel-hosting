<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use LRV\Core\BancoDeDados;
use LRV\Core\Bootstrap;

Bootstrap::iniciar();

$pdo = BancoDeDados::pdo();

$diretorio = __DIR__ . '/database/migrations';
if (!is_dir($diretorio)) {
    echo "Pasta de migrations não encontrada.\n";
    exit(1);
}

$arquivos = glob($diretorio . '/*.sql') ?: [];
sort($arquivos);

$stmt = $pdo->query('SELECT file_name FROM migrations');
$executadas = [];
foreach ($stmt->fetchAll() as $l) {
    $executadas[(string) ($l['file_name'] ?? '')] = true;
}

$novas = 0;

foreach ($arquivos as $arquivo) {
    $nome = basename($arquivo);
    if (isset($executadas[$nome])) {
        continue;
    }

    $sql = (string) file_get_contents($arquivo);
    $sql = trim($sql);

    if ($sql === '') {
        continue;
    }

    echo "Executando migration: {$nome}\n";

    $pdo->beginTransaction();
    try {
        foreach (separarSqlEmComandos($sql) as $cmd) {
            $cmd = trim($cmd);
            if ($cmd === '') {
                continue;
            }
            $pdo->exec($cmd);
        }

        $ins = $pdo->prepare('INSERT INTO migrations (file_name, executed_at) VALUES (:f, :e)');
        $ins->execute([
            ':f' => $nome,
            ':e' => date('Y-m-d H:i:s'),
        ]);

        $pdo->commit();
        $novas++;
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "Falha na migration {$nome}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo $novas === 0 ? "Nenhuma migration pendente.\n" : "Migrations executadas: {$novas}\n";

function separarSqlEmComandos(string $sql): array
{
    $linhas = preg_split('/\R/', $sql) ?: [];
    $comandos = [];
    $buffer = '';

    foreach ($linhas as $linha) {
        $trim = trim($linha);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }

        $buffer .= $linha . "\n";

        if (str_ends_with(rtrim($linha), ';')) {
            $comandos[] = $buffer;
            $buffer = '';
        }
    }

    if (trim($buffer) !== '') {
        $comandos[] = $buffer;
    }

    return $comandos;
}
