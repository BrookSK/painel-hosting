<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$nome = (string) ($usuario['name'] ?? '');
$email = (string) ($usuario['email'] ?? '');
$role = (string) ($usuario['role'] ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel da equipe</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Painel da equipe</div>
        <div style="opacity:.9; font-size:13px;">Você está logado como <?php echo View::e($nome); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/">Início</a>
        <a href="/equipe/ajuda">Ajuda</a>
        <a href="/equipe/notificacoes">Notificações</a>
        <a href="/equipe/terminal">Terminal</a>
        <a href="/equipe/usuarios">Usuários</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/assinaturas">Assinaturas</a>
        <a href="/equipe/asaas-eventos">Eventos Asaas</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/monitoramento">Monitoramento</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/backups">Backups</a>
        <a href="/equipe/aplicacoes">Aplicações</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/inicializacao">Inicialização</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="grid">
      <div class="card">
        <h2 class="titulo">Seu acesso</h2>
        <p class="texto"><strong>Usuário:</strong> <?php echo View::e($nome); ?></p>
        <p class="texto"><strong>E-mail:</strong> <?php echo View::e($email); ?></p>
        <p class="texto"><strong>Perfil:</strong> <?php echo View::e($role); ?></p>
      </div>

      <div class="card">
        <h2 class="titulo">Próximos módulos</h2>
        <p class="texto">Agora eu vou conectar:</p>
        <div class="linha" style="gap:8px;">
          <span class="badge">Planos</span>
          <span class="badge">Cobrança (Asaas)</span>
          <span class="badge">Cluster/Nodes</span>
          <span class="badge">Provisionamento</span>
          <span class="badge">Tickets</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
