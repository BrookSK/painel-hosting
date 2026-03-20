<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$nome  = (string) ($usuario['name'] ?? '');
$email = (string) ($usuario['email'] ?? '');
$role  = (string) ($usuario['role'] ?? '');

$totalVps       = (int) ($metricas['total_vps'] ?? 0);
$vpsRunning     = (int) ($metricas['vps_running'] ?? 0);
$totalClientes  = (int) ($metricas['total_clientes'] ?? 0);
$totalTickets   = (int) ($metricas['tickets_abertos'] ?? 0);
$jobsPendentes  = (int) ($metricas['jobs_pendentes'] ?? 0);
$nodesOnline    = (int) ($metricas['nodes_online'] ?? 0);
$receitaMensal  = (string) ($metricas['receita_mensal'] ?? '0.00');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel da equipe</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    .metric-card{border:1px solid #e5e7eb;border-radius:14px;padding:18px;background:#fff;box-shadow:0 4px 12px rgba(15,23,42,.05);}
    .metric-val{font-size:32px;font-weight:700;color:#4F46E5;margin:6px 0 2px;}
    .metric-lbl{font-size:13px;color:#64748b;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Painel da equipe</div>
        <div style="opacity:.9; font-size:13px;">Olá, <?php echo View::e($nome); ?></div>
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
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:20px;">
      <div class="metric-card">
        <div class="metric-lbl">VPS ativas</div>
        <div class="metric-val"><?php echo $vpsRunning; ?></div>
        <div class="metric-lbl">de <?php echo $totalVps; ?> total</div>
      </div>
      <div class="metric-card">
        <div class="metric-lbl">Clientes</div>
        <div class="metric-val"><?php echo $totalClientes; ?></div>
      </div>
      <div class="metric-card">
        <div class="metric-lbl">Tickets abertos</div>
        <div class="metric-val"><?php echo $totalTickets; ?></div>
      </div>
      <div class="metric-card">
        <div class="metric-lbl">Jobs pendentes</div>
        <div class="metric-val"><?php echo $jobsPendentes; ?></div>
      </div>
      <div class="metric-card">
        <div class="metric-lbl">Nodes online</div>
        <div class="metric-val"><?php echo $nodesOnline; ?></div>
      </div>
      <div class="metric-card">
        <div class="metric-lbl">Receita mensal</div>
        <div class="metric-val" style="font-size:22px;">R$ <?php echo View::e($receitaMensal); ?></div>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <h2 class="titulo">Seu acesso</h2>
        <p class="texto"><strong>Usuário:</strong> <?php echo View::e($nome); ?></p>
        <p class="texto"><strong>E-mail:</strong> <?php echo View::e($email); ?></p>
        <p class="texto"><strong>Perfil:</strong> <?php echo View::e($role); ?></p>
        <div class="linha" style="gap:8px; margin-top:10px;">
          <a class="botao" href="/equipe/2fa/configurar" style="font-size:13px; padding:8px 12px;">2FA</a>
          <a class="botao sec" href="/equipe/permissoes" style="font-size:13px; padding:8px 12px;">Permissões</a>
        </div>
      </div>

      <div class="card">
        <h2 class="titulo">Ações rápidas</h2>
        <div style="display:flex; flex-direction:column; gap:8px;">
          <a href="/equipe/vps">Gerenciar VPS</a>
          <a href="/equipe/tickets">Ver tickets</a>
          <a href="/equipe/jobs">Ver jobs</a>
          <a href="/equipe/servidores">Servidores / Nodes</a>
          <a href="/equipe/monitoramento">Monitoramento</a>
          <a href="/equipe/assinaturas">Assinaturas</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
