<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeSvcStatusEquipe(string $st): string
{
    if ($st === 'operational') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Operacional</span>';
    }
    if ($st === 'degraded') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Degradado</span>';
    }
    if ($st === 'major_outage') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Indisponível</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">Desconhecido</span>';
}

function badgeIncidentStatusEquipe(string $st): string
{
    if ($st === 'resolved') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Resolvido</span>';
    }
    if ($st === 'monitoring') {
        return '<span class="badge" style="background:#e0f2fe;color:#075985;">Monitorando</span>';
    }
    if ($st === 'identified') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Identificado</span>';
    }
    return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Investigando</span>';
}

function badgeImpactEquipe(string $impact): string
{
    if ($impact === 'critical') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Crítico</span>';
    }
    if ($impact === 'major') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Alto</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">Baixo</span>';
}

$servicesArr = is_array($services ?? null) ? $services : [];
$incidentsArr = is_array($incidents ?? null) ? $incidents : [];
$incidentServicesMap = is_array($incidentServices ?? null) ? $incidentServices : [];

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Status (Equipe)</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Status</div>
        <div style="opacity:.9; font-size:13px;">Dashboard e incidentes</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/inicializacao">Inicialização</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="margin-bottom:14px;">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Serviços coletados</h2>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Key</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Nome</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Scope</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Node</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Última checagem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicesArr as $s): ?>
              <?php if (!is_array($s)) continue; ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['key'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string) ($s['name'] ?? '')); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['scope'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo (int) ($s['client_id'] ?? 0); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo (int) ($s['server_id'] ?? 0); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo (int) ($s['vps_id'] ?? 0); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeSvcStatusEquipe((string) ($s['status'] ?? 'unknown')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['last_check_at'] ?? '')); ?></code></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servicesArr)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Sem dados ainda. Inicie a coleta em Inicialização.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Criar incidente</h2>

      <form method="post" action="/equipe/status/incidentes/criar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="grid" style="margin-bottom:10px;">
          <div>
            <div class="texto" style="margin:0 0 6px 0;"><strong>Título</strong></div>
            <input class="input" name="title" placeholder="Ex: Instabilidade no node" />
          </div>
          <div>
            <div class="texto" style="margin:0 0 6px 0;"><strong>Impacto</strong></div>
            <select class="input" name="impact">
              <option value="minor">Baixo</option>
              <option value="major">Alto</option>
              <option value="critical">Crítico</option>
            </select>
          </div>
          <div>
            <div class="texto" style="margin:0 0 6px 0;"><strong>Scope</strong></div>
            <select class="input" name="scope">
              <option value="public">Público</option>
              <option value="client">Cliente</option>
              <option value="internal">Interno</option>
            </select>
          </div>
          <div>
            <div class="texto" style="margin:0 0 6px 0;"><strong>Serviços (IDs)</strong></div>
            <input class="input" name="service_ids" placeholder="Ex: 12, 13, 14" />
          </div>
        </div>

        <div style="margin-bottom:10px;">
          <div class="texto" style="margin:0 0 6px 0;"><strong>Mensagem</strong></div>
          <textarea class="input" name="message" rows="4" placeholder="Descreva o incidente..."></textarea>
        </div>

        <button class="botao" type="submit">Criar incidente</button>
      </form>
    </div>

    <div class="card">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Incidentes</h2>

      <?php if (empty($incidentsArr)): ?>
        <div class="texto" style="margin:0;">Nenhum incidente.</div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <?php foreach ($incidentsArr as $inc): ?>
            <?php if (!is_array($inc)) continue; ?>
            <?php $iid = (int) ($inc['id'] ?? 0); ?>
            <?php $svc = $incidentServicesMap[$iid] ?? []; ?>
            <?php $svcIds = []; foreach (($svc ?: []) as $sv) { if (is_array($sv)) { $sid = (int) ($sv['service_id'] ?? 0); if ($sid > 0) { $svcIds[] = $sid; } } } ?>
            <?php $svcIdsTxt = implode(',', $svcIds); ?>
            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
              <div class="linha" style="justify-content:space-between;">
                <div>
                  <div><strong>#<?php echo $iid; ?> - <?php echo View::e((string) ($inc['title'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8; margin-top:2px;">Início: <?php echo View::e((string) ($inc['started_at'] ?? '')); ?><?php if (trim((string) ($inc['resolved_at'] ?? '')) !== ''): ?> | Resolvido: <?php echo View::e((string) ($inc['resolved_at'] ?? '')); ?><?php endif; ?></div>
                </div>
                <div class="linha">
                  <?php echo badgeImpactEquipe((string) ($inc['impact'] ?? 'minor')); ?>
                  <?php echo badgeIncidentStatusEquipe((string) ($inc['status'] ?? 'investigating')); ?>
                  <span class="badge" style="background:#f1f5f9;color:#334155;"><?php echo View::e((string) ($inc['scope'] ?? 'public')); ?></span>
                </div>
              </div>

              <?php if (trim((string) ($inc['message'] ?? '')) !== ''): ?>
                <div class="texto" style="margin:10px 0 0 0;"><?php echo nl2br(View::e((string) ($inc['message'] ?? ''))); ?></div>
              <?php endif; ?>

              <?php if (!empty($svc)): ?>
                <div style="margin-top:10px;">
                  <div class="texto" style="margin:0 0 6px 0;"><strong>Serviços afetados</strong></div>
                  <div class="linha">
                    <?php foreach ($svc as $sv): ?>
                      <?php if (!is_array($sv)) continue; ?>
                      <span class="badge" style="background:#f1f5f9;color:#334155;"><code><?php echo (int) ($sv['service_id'] ?? 0); ?></code> <?php echo View::e((string) ($sv['name'] ?? '')); ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <div style="margin-top:10px; border-top:1px solid #e5e7eb; padding-top:10px;">
                <form method="post" action="/equipe/status/incidentes/servicos" class="grid" style="align-items:end;">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                  <input type="hidden" name="incident_id" value="<?php echo $iid; ?>" />
                  <div style="grid-column: span 3;">
                    <div class="texto" style="margin:0 0 6px 0;"><strong>Serviços (IDs)</strong></div>
                    <input class="input" name="service_ids" value="<?php echo View::e($svcIdsTxt); ?>" placeholder="Ex: 12, 13, 14" />
                  </div>
                  <div>
                    <button class="botao sec" type="submit">Salvar serviços</button>
                  </div>
                </form>
              </div>

              <div style="margin-top:10px; border-top:1px solid #e5e7eb; padding-top:10px;">
                <form method="post" action="/equipe/status/incidentes/atualizar" class="grid" style="align-items:end;">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                  <input type="hidden" name="incident_id" value="<?php echo $iid; ?>" />
                  <div>
                    <div class="texto" style="margin:0 0 6px 0;"><strong>Status</strong></div>
                    <select class="input" name="status">
                      <option value="investigating">Investigando</option>
                      <option value="identified">Identificado</option>
                      <option value="monitoring">Monitorando</option>
                      <option value="resolved">Resolvido</option>
                    </select>
                  </div>
                  <div style="grid-column: span 2;">
                    <div class="texto" style="margin:0 0 6px 0;"><strong>Mensagem</strong></div>
                    <input class="input" name="message" placeholder="Atualização do incidente..." />
                  </div>
                  <div>
                    <button class="botao" type="submit">Adicionar update</button>
                  </div>
                </form>
              </div>

              <?php $up = $updates[$iid] ?? []; ?>
              <?php if (!empty($up)): ?>
                <div style="margin-top:10px;">
                  <div class="texto" style="margin:0 0 6px 0;"><strong>Últimas atualizações</strong></div>
                  <div style="display:flex; flex-direction:column; gap:6px;">
                    <?php foreach ($up as $u): ?>
                      <?php if (!is_array($u)) continue; ?>
                      <div style="border-left:3px solid #e5e7eb; padding-left:10px;">
                        <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($u['created_at'] ?? '')); ?> - <?php echo View::e((string) ($u['status'] ?? '')); ?></div>
                        <div class="texto" style="margin:2px 0 0 0;"><?php echo nl2br(View::e((string) ($u['message'] ?? ''))); ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
