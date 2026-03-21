<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$dominios   = is_array($dominios ?? null) ? $dominios : [];
$verificado = isset($_GET['verificado']);

function badgeDominio(string $status): string
{
    if ($status === 'active')      return '<span class="badge badge-verde">Ativo</span>';
    if ($status === 'pending_dns') return '<span class="badge badge-amarelo">Aguardando DNS</span>';
    return '<span class="badge badge-vermelho">Erro</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Meus Domínios de E-mail</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Domínios de E-mail</div>
        <div style="opacity:.9;font-size:13px;">Use seu próprio domínio para criar e-mails profissionais</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/emails">← E-mails</a>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">

    <?php if (!empty($erro)): ?>
      <div class="erro"><?php echo View::e((string)$erro); ?></div>
    <?php endif; ?>

    <?php if ($verificado): ?>
      <div class="sucesso">DNS verificado com sucesso! Seu domínio está ativo.</div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns:1fr 340px;gap:16px;align-items:start;">

      <div class="card">
        <h1 class="titulo" style="margin-bottom:6px;">Seus domínios</h1>
        <p class="texto" style="margin-bottom:14px;">
          Domínios com status <strong>Ativo</strong> podem ser usados ao criar e-mails.
          Domínios <strong>Aguardando DNS</strong> precisam ter os registros configurados no seu provedor.
        </p>

        <?php if (empty($dominios)): ?>
          <div style="text-align:center;padding:32px 0;color:#94a3b8;">
            <div style="font-size:32px;margin-bottom:8px;">🌐</div>
            <div style="font-size:14px;">Nenhum domínio cadastrado ainda.</div>
            <div style="font-size:13px;margin-top:4px;">Adicione seu domínio ao lado para começar.</div>
          </div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($dominios as $d): ?>
              <?php $did = (int)($d['id'] ?? 0); ?>
              <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
                <div class="linha" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
                  <div>
                    <div style="font-weight:600;font-size:14px;"><?php echo View::e((string)($d['domain'] ?? '')); ?></div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Adicionado em <?php echo View::e((string)($d['created_at'] ?? '')); ?></div>
                  </div>
                  <div class="linha" style="gap:6px;">
                    <?php echo badgeDominio((string)($d['status'] ?? '')); ?>
                  </div>
                </div>

                <?php if (!empty($d['error_msg'])): ?>
                  <div style="margin-top:8px;font-size:12px;color:#ef4444;background:#fef2f2;padding:6px 10px;border-radius:8px;">
                    <?php echo View::e((string)$d['error_msg']); ?>
                  </div>
                <?php endif; ?>

                <div class="linha" style="margin-top:10px;gap:8px;flex-wrap:wrap;">
                  <a href="/cliente/emails/dominios/instrucoes?id=<?php echo $did; ?>" class="botao sm ghost">Ver instruções DNS</a>

                  <?php if (($d['status'] ?? '') !== 'active'): ?>
                    <form method="post" action="/cliente/emails/dominios/verificar" style="display:inline;">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                      <input type="hidden" name="dominio_id" value="<?php echo $did; ?>" />
                      <button class="botao sm" type="submit">Verificar DNS</button>
                    </form>
                  <?php endif; ?>

                  <form method="post" action="/cliente/emails/dominios/remover" style="display:inline;"
                        onsubmit="return confirm('Remover o domínio <?php echo View::e((string)($d['domain'] ?? '')); ?>?')">
                    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                    <input type="hidden" name="dominio_id" value="<?php echo $did; ?>" />
                    <button class="botao danger sm" type="submit">Remover</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div>
        <div class="card">
          <h2 class="titulo" style="margin-bottom:6px;">Adicionar domínio</h2>
          <p class="texto" style="font-size:13px;margin-bottom:14px;">
            Informe o domínio que você controla. Após adicionar, você receberá as instruções DNS para configurar.
          </p>
          <form method="post" action="/cliente/emails/dominios/adicionar">
            <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
            <div style="margin-bottom:10px;">
              <label style="display:block;font-size:13px;margin-bottom:5px;">Domínio</label>
              <input class="input" type="text" name="domain" placeholder="meudominio.com.br" required
                     pattern="[a-z0-9][a-z0-9\-\.]*\.[a-z]{2,}" title="Ex: meudominio.com" />
            </div>
            <button class="botao" type="submit">Adicionar domínio</button>
          </form>
        </div>

        <div class="card" style="margin-top:14px;">
          <h2 class="titulo" style="font-size:15px;margin-bottom:8px;">Como funciona?</h2>
          <ol style="padding-left:18px;color:#475569;font-size:13px;line-height:1.8;">
            <li>Adicione seu domínio acima</li>
            <li>Clique em <strong>Ver instruções DNS</strong></li>
            <li>Configure os registros no seu provedor de DNS</li>
            <li>Clique em <strong>Verificar DNS</strong></li>
            <li>Crie e-mails usando seu domínio em <a href="/cliente/emails">Meus E-mails</a></li>
          </ol>
        </div>
      </div>

    </div>
  </div>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
