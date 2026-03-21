<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class ChatUploadController
{
    private const MAX_SIZE  = 5 * 1024 * 1024; // 5 MB
    private const TIPOS_OK  = [
        'image/png', 'image/jpeg', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
    ];
    private const EXTS_OK = ['png','jpg','jpeg','gif','webp','pdf','doc','docx','txt'];

    public function upload(Requisicao $req): Resposta
    {
        // Aceitar tanto cliente quanto equipe
        $clienteId = Auth::clienteId();
        $equipeId  = Auth::equipeId();
        if ($clienteId === null && $equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $file = $_FILES['arquivo'] ?? null;
        if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Resposta::json(['ok' => false, 'erro' => 'Nenhum arquivo enviado.'], 400);
        }

        if ((int)($file['size'] ?? 0) > self::MAX_SIZE) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo muito grande (máx. 5 MB).'], 400);
        }

        $tmpPath = (string)($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmpPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo inválido.'], 400);
        }

        $mime = mime_content_type($tmpPath);
        if (!in_array($mime, self::TIPOS_OK, true)) {
            return Resposta::json(['ok' => false, 'erro' => 'Tipo não permitido.'], 400);
        }

        $origName = (string)($file['name'] ?? 'arquivo');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXTS_OK, true)) {
            $mimeMap = [
                'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif',
                'image/webp' => 'webp', 'application/pdf' => 'pdf', 'text/plain' => 'txt',
            ];
            $ext = $mimeMap[$mime] ?? 'bin';
        }

        $dir = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        $uploadDir = $dir . '/public/uploads/chat';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $nome = bin2hex(random_bytes(12)) . '.' . $ext;
        $destino = $uploadDir . '/' . $nome;

        if (!move_uploaded_file($tmpPath, $destino)) {
            return Resposta::json(['ok' => false, 'erro' => 'Falha ao salvar.'], 500);
        }

        return Resposta::json([
            'ok'        => true,
            'file_url'  => '/uploads/chat/' . $nome,
            'file_name' => $origName,
        ]);
    }
}
