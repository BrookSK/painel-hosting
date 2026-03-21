<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class ImagemUploadController
{
    private const MAX_SIZE  = 2 * 1024 * 1024; // 2 MB
    private const TIPOS_OK  = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
    private const EXTS_OK   = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'];
    private const UPLOAD_DIR = 'public/uploads/imagens';

    public function upload(Requisicao $req): Resposta
    {
        $file = $_FILES['imagem'] ?? null;

        if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Resposta::json(['ok' => false, 'erro' => 'Nenhum arquivo enviado.'], 400);
        }

        $size = (int)($file['size'] ?? 0);
        if ($size > self::MAX_SIZE) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo muito grande (máx. 2 MB).'], 400);
        }

        $tmpPath = (string)($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmpPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo inválido.'], 400);
        }

        // Validar MIME real (não confiar no cliente)
        $mime = mime_content_type($tmpPath);
        if (!in_array($mime, self::TIPOS_OK, true)) {
            return Resposta::json(['ok' => false, 'erro' => 'Tipo de arquivo não permitido.'], 400);
        }

        $origName = (string)($file['name'] ?? 'imagem');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXTS_OK, true)) {
            // Derivar extensão do MIME
            $mimeMap = [
                'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif',
                'image/webp' => 'webp', 'image/svg+xml' => 'svg',
                'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico',
            ];
            $ext = $mimeMap[$mime] ?? 'png';
        }

        $dir = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
        // Fallback: usar BASE_PATH se definido, senão dirname do index
        if ($dir === '') {
            $dir = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        }

        $uploadDir = $dir . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $nome = bin2hex(random_bytes(12)) . '.' . $ext;
        $destino = $uploadDir . '/' . $nome;

        if (!move_uploaded_file($tmpPath, $destino)) {
            return Resposta::json(['ok' => false, 'erro' => 'Falha ao salvar o arquivo.'], 500);
        }

        // URL pública relativa
        $url = '/uploads/imagens/' . $nome;

        return Resposta::json(['ok' => true, 'url' => $url]);
    }
}
