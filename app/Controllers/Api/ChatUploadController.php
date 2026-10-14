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
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            ];
            $ext = $mimeMap[$mime] ?? 'bin';
        }

        if ($ext === 'bin') {
            return Resposta::json(['ok' => false, 'erro' => 'Tipo não permitido.'], 400);
        }

        // Anexos ficam FORA da pasta pública e só são entregues por baixar(), que confere
        // se quem pede tem direito ao arquivo.
        $uploadDir = self::dirPrivado();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }

        $nome = bin2hex(random_bytes(12)) . '.' . $ext;
        $destino = $uploadDir . '/' . $nome;

        if (!move_uploaded_file($tmpPath, $destino)) {
            return Resposta::json(['ok' => false, 'erro' => 'Falha ao salvar.'], 500);
        }
        @chmod($destino, 0640);

        // Quem enviou pode ver o próprio arquivo antes de a mensagem ser gravada
        $_SESSION['chat_uploads'] = array_slice(array_merge($_SESSION['chat_uploads'] ?? [], [$nome]), -50);

        return Resposta::json([
            'ok'        => true,
            'file_url'  => '/chat/arquivo/' . $nome,
            'file_name' => mb_substr($origName, 0, 120),
        ]);
    }

    /**
     * GET /chat/arquivo/{nome} (e o endereço antigo /uploads/chat/{nome}).
     * Equipe logada vê qualquer anexo; cliente só os da própria conversa.
     */
    public function baixar(Requisicao $req): Resposta
    {
        $nome = (string) ($req->params['nome'] ?? '');
        if (preg_match('/^[a-f0-9]{24}\.(png|jpe?g|gif|webp|pdf|docx?|txt)$/', $nome) !== 1) {
            return Resposta::texto('Arquivo não encontrado.', 404);
        }

        $clienteId = Auth::clienteId();
        $equipeId  = Auth::equipeId();
        if ($clienteId === null && $equipeId === null) {
            return Resposta::texto('Arquivo não encontrado.', 404);
        }

        if ($equipeId === null) {
            $permitido = in_array($nome, $_SESSION['chat_uploads'] ?? [], true);
            if (!$permitido) {
                $st = \LRV\Core\BancoDeDados::pdo()->prepare(
                    'SELECT 1 FROM chat_messages m INNER JOIN chat_rooms r ON r.id = m.room_id
                     WHERE r.client_id = :c AND m.file_url IN (:u1, :u2) LIMIT 1'
                );
                $st->execute([':c' => $clienteId, ':u1' => '/chat/arquivo/' . $nome, ':u2' => '/uploads/chat/' . $nome]);
                $permitido = (bool) $st->fetchColumn();
            }
            if (!$permitido) {
                return Resposta::texto('Arquivo não encontrado.', 404);
            }
        }

        // Novo local (privado) e, para anexos antigos, o local público anterior
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        $path = self::dirPrivado() . '/' . $nome;
        if (!is_file($path)) {
            $path = $base . '/public/uploads/chat/' . $nome;
        }
        if (!is_file($path)) {
            return Resposta::texto('Arquivo não encontrado.', 404);
        }

        $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
        $tipos = [
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'pdf' => 'application/pdf', 'txt' => 'text/plain; charset=utf-8',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $imagem = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true);

        return Resposta::arquivo($path, $nome, $tipos[$ext] ?? 'application/octet-stream')->comHeaders([
            // Imagens abrem no chat; o resto sempre baixa (nunca é interpretado pelo navegador)
            'Content-Disposition' => ($imagem ? 'inline' : 'attachment') . '; filename="' . $nome . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private static function dirPrivado(): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        return $base . '/storage/uploads/chat';
    }
}
