<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$servidor = (array) ($servidor ?? []);
$id = (int) ($servidor['id'] ?? 0);
$host = trim((string) ($servidor['ip_address'] ?? ''));
$porta = (int) ($servidor['ssh_port'] ?? 22);
$terminalUser = trim((string) ($servidor['terminal_ssh_user'] ?? 'lrv-terminal'));
$terminalKeyId = trim((string) ($servidor['terminal_ssh_key_id'] ?? ''));

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Terminal seguro - Node #<?php echo (int) $id; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Terminal seguro (clientes)</div>
        <div style="opacity:.9; font-size:13px;">Passo-a-passo de configuração no node</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/servidores/editar?id=<?php echo (int) $id; ?>">Voltar</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:980px; margin:0 auto;">
      <h1 class="titulo">Node #<?php echo (int) $id; ?> - <?php echo View::e((string) ($servidor['hostname'] ?? '')); ?></h1>

      <div class="texto" style="margin:0 0 10px 0; opacity:.9;">
        O objetivo é permitir um terminal <strong>interativo</strong> para o cliente, porém isolado dentro do contêiner da VPS.
        Isso é feito criando um usuário SSH restrito (ex.: <code>lrv-terminal</code>) com <code>ForceCommand</code> apontando para um wrapper.
      </div>

      <div class="card" style="margin:0 0 12px 0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>Pré-requisitos</strong></div>
        <div class="texto" style="margin:0; font-size:13px; opacity:.9;">
          O usuário SSH configurado deve ter permissão para executar <code>docker exec</code> no node (tipicamente via grupo <code>docker</code>).
          O acesso root não é necessário.
        </div>
      </div>

      <div class="card" style="margin:0 0 12px 0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>Dados deste node</strong></div>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
          <div>
            <div class="texto" style="margin:0; font-size:13px; opacity:.9;">Host</div>
            <div style="margin-top:6px;"><code><?php echo View::e($host); ?></code></div>
          </div>
          <div>
            <div class="texto" style="margin:0; font-size:13px; opacity:.9;">Porta</div>
            <div style="margin-top:6px;"><code><?php echo (int) $porta; ?></code></div>
          </div>
          <div>
            <div class="texto" style="margin:0; font-size:13px; opacity:.9;">Usuário do terminal</div>
            <div style="margin-top:6px;"><code><?php echo View::e($terminalUser); ?></code></div>
          </div>
          <div>
            <div class="texto" style="margin:0; font-size:13px; opacity:.9;">Chave (id)</div>
            <div style="margin-top:6px;"><code><?php echo View::e($terminalKeyId !== '' ? $terminalKeyId : '(não configurado)'); ?></code></div>
          </div>
        </div>
      </div>

      <div class="card" style="margin:0 0 12px 0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>1) Criar usuário restrito</strong></div>
        <pre style="white-space:pre-wrap; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:12px; overflow:auto; margin-top:6px;">sudo useradd -m -s /usr/sbin/nologin <?php echo View::e($terminalUser); ?> || sudo useradd -m -s /bin/bash <?php echo View::e($terminalUser); ?>
# Dar permissão para docker exec (sem root)
sudo usermod -aG docker <?php echo View::e($terminalUser); ?></pre>
      </div>

      <div class="card" style="margin:0 0 12px 0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>2) Instalar wrapper (ForceCommand)</strong></div>
        <div class="texto" style="margin:0; font-size:13px; opacity:.9;">
          Crie o script abaixo em <code>/usr/local/bin/lrv-terminal</code> e dê permissão de execução.
          Ele valida o contexto recebido e abre um shell dentro do contêiner via <code>docker exec</code>.
        </div>
        <pre style="white-space:pre; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:12px; overflow:auto; margin-top:10px;">#!/usr/bin/env bash
set -euo pipefail

MODE=""
CLIENT_ID=""
VPS_ID=""
CONTAINER_ID=""
SESSION=""

# O painel conecta com: lrv-terminal --mode=client --vps-id=123 --container-id=abc --session=uuid
ORIG="${SSH_ORIGINAL_COMMAND:-}"

# Parse simples (sem eval)
read -r -a ARGS <<< "$ORIG"

if [[ "${#ARGS[@]}" -lt 1 ]]; then
  echo "Comando inválido." >&2
  exit 1
fi

if [[ "${ARGS[0]}" != "lrv-terminal" ]]; then
  echo "Comando inválido." >&2
  exit 1
fi

for a in "${ARGS[@]:1}"; do
  case "$a" in
    --mode=*) MODE="${a#--mode=}";;
    --client-id=*) CLIENT_ID="${a#--client-id=}";;
    --vps-id=*) VPS_ID="${a#--vps-id=}";;
    --container-id=*) CONTAINER_ID="${a#--container-id=}";;
    --session=*) SESSION="${a#--session=}";;
  esac
done

if [[ "$MODE" == "client" ]]; then
  if [[ -z "$CLIENT_ID" || -z "$VPS_ID" || -z "$CONTAINER_ID" ]]; then
    echo "Contexto ausente." >&2
    exit 1
  fi

  if [[ ! "$CLIENT_ID" =~ ^[0-9]+$ ]]; then
    echo "Cliente inválido." >&2
    exit 1
  fi

  if [[ ! "$VPS_ID" =~ ^[0-9]+$ ]]; then
    echo "VPS inválida." >&2
    exit 1
  fi

  if [[ ! "$CONTAINER_ID" =~ ^[a-zA-Z0-9][a-zA-Z0-9_.-]+$ ]]; then
    echo "Container inválido." >&2
    exit 1
  fi

  # Opcional: validar se o container existe
  if ! docker inspect "$CONTAINER_ID" >/dev/null 2>&1; then
    echo "Container não encontrado." >&2
    exit 1
  fi

  # Validar ownership via labels
  LABELS=$(docker inspect -f '{{ index .Config.Labels "lrv.vps_id" }}|{{ index .Config.Labels "lrv.client_id" }}' "$CONTAINER_ID" 2>/dev/null || true)
  L_VPS="${LABELS%%|*}"
  L_CLIENT="${LABELS#*|}"

  if [[ -z "$L_VPS" || -z "$L_CLIENT" ]]; then
    echo "Container sem labels de ownership." >&2
    exit 1
  fi

  if [[ "$L_VPS" != "$VPS_ID" || "$L_CLIENT" != "$CLIENT_ID" ]]; then
    echo "Container não pertence à VPS/cliente." >&2
    exit 1
  fi

  # Log básico (syslog)
  logger -t lrv-terminal "mode=client vps_id=$VPS_ID container=$CONTAINER_ID user=$USER session=$SESSION"

  # Terminal interativo no container
  exec docker exec -it "$CONTAINER_ID" bash
fi

echo "Modo inválido." >&2
exit 1</pre>
        <pre style="white-space:pre-wrap; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:12px; overflow:auto; margin-top:10px;">sudo chown root:root /usr/local/bin/lrv-terminal
sudo chmod 0755 /usr/local/bin/lrv-terminal</pre>
      </div>

      <div class="card" style="margin:0 0 12px 0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>3) Configurar ForceCommand no sshd</strong></div>
        <div class="texto" style="margin:0; font-size:13px; opacity:.9;">
          No arquivo <code>/etc/ssh/sshd_config</code>, adicione um bloco <code>Match User</code>.
        </div>
        <pre style="white-space:pre-wrap; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:12px; overflow:auto; margin-top:10px;">Match User <?php echo View::e($terminalUser); ?>
  ForceCommand /usr/local/bin/lrv-terminal
  PermitTTY yes
  AllowAgentForwarding no
  PermitUserEnvironment no
  X11Forwarding no
  AllowTcpForwarding no
  GatewayPorts no
  PermitTunnel no</pre>
        <pre style="white-space:pre-wrap; background:#0b1020; color:#e5e7eb; padding:10px; border-radius:12px; overflow:auto; margin-top:10px;">sudo systemctl restart sshd</pre>
      </div>

      <div class="card" style="margin:0;">
        <div class="texto" style="margin:0 0 6px 0;"><strong>Observações importantes</strong></div>
        <div class="texto" style="margin:0; font-size:13px; opacity:.9;">
          - Este fluxo garante que clientes só entram no contêiner (via <code>docker exec</code>), nunca no host.
          - A auditoria completa fica no painel: tokens, sessões e comandos (tabelas <code>client_terminal_*</code>).
          - O wrapper acima é propositalmente simples. Podemos evoluir para:
            validar se o <code>container_id</code> realmente pertence ao <code>vps_id</code> via label/metadata no Docker.
        </div>
      </div>
    </div>
  </div>
</body>
</html>
