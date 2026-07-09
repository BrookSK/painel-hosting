<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
$t = fn(string $k) => I18n::t($k);
$titulo = $titulo ?? $t('api_docs.titulo');
$baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'cloud.lrvweb.com.br');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;overflow-x:hidden}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}
.doc-hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);padding:100px 24px 60px;text-align:center;position:relative;overflow:hidden}
.doc-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.doc-hero h1{font-size:clamp(28px,5vw,42px);font-weight:900;color:#fff;margin-bottom:12px;position:relative}
.doc-hero p{font-size:16px;color:rgba(255,255,255,.7);max-width:560px;margin:0 auto;position:relative}
.doc-nav{background:#0f172a;padding:12px 24px;position:sticky;top:0;z-index:50;border-bottom:1px solid #1e293b}
.doc-nav-inner{max-width:1100px;margin:0 auto;display:flex;gap:6px;flex-wrap:wrap;justify-content:center}
.doc-nav a{padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;color:rgba(255,255,255,.55);text-decoration:none;transition:all .15s}
.doc-nav a:hover,.doc-nav a.active{background:rgba(255,255,255,.1);color:#fff}
.doc-content{max-width:900px;margin:0 auto;padding:48px 24px}
.doc-section{margin-bottom:64px}
.doc-section h2{font-size:24px;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #e2e8f0}
.doc-section h3{font-size:16px;font-weight:700;color:#1e293b;margin:24px 0 10px}
.doc-section p,.doc-section li{font-size:14px;color:#475569;line-height:1.8}
.doc-section ul,.doc-section ol{padding-left:20px;margin:8px 0}
.doc-section code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:13px;color:#4F46E5;font-family:'JetBrains Mono',monospace}
.code-tabs{margin:16px 0;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
.code-tabs-nav{display:flex;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:0}
.code-tabs-nav button{padding:10px 16px;border:none;background:none;font-size:12px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s}
.code-tabs-nav button.active{color:#4F46E5;border-bottom-color:#4F46E5;background:#fff}
.code-tabs-nav button:hover{color:#0f172a}
.code-panel{display:none;background:#0f172a;padding:20px;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:12.5px;color:#e2e8f0;line-height:1.7}
.code-panel.active{display:block}
.code-panel .cm{color:#475569}
.code-panel .str{color:#86efac}
.code-panel .kw{color:#c4b5fd}
.code-panel .fn{color:#67e8f9}
.code-panel .num{color:#fbbf24}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="doc-hero">
    <h1><?= View::e($titulo) ?></h1>
    <p><?= $t('api_docs.subtitulo') ?></p>
</section>

<nav class="doc-nav">
    <div class="doc-nav-inner">
        <a href="#introducao"><?= $t('api_docs.nav_intro') ?></a>
        <a href="#autenticacao"><?= $t('api_docs.nav_auth') ?></a>
        <a href="#primeiros-passos">Primeiros Passos</a>
        <a href="#endpoints"><?= $t('api_docs.nav_endpoints') ?></a>
        <a href="#paginacao">Paginação</a>
        <a href="#rate-limit"><?= $t('api_docs.nav_rate_limit') ?></a>
        <a href="#webhooks"><?= $t('api_docs.nav_webhooks') ?></a>
        <a href="#erros"><?= $t('api_docs.nav_erros') ?></a>
        <a href="#sdks"><?= $t('api_docs.nav_sdks') ?></a>
        <a href="/developers/api/swagger">API Explorer</a>
    </div>
</nav>

<div class="doc-content">

<!-- INTRODUÇÃO -->
<section class="doc-section" id="introducao">
<h2><?= $t('api_docs.intro_titulo') ?></h2>
<p><?= $t('api_docs.intro_desc') ?></p>
<p>Base URL: <code><?= $baseUrl ?>/api/v1/</code></p>
<p>Formato: todas as respostas são JSON. Envie <code>Content-Type: application/json</code> no body de POST/PUT.</p>

<div class="code-tabs">
<div class="code-tabs-nav">
<button class="active" onclick="showTab(this,'intro')">cURL</button>
<button onclick="showTab(this,'intro')">PHP</button>
<button onclick="showTab(this,'intro')">JavaScript</button>
<button onclick="showTab(this,'intro')">Python</button>
</div>
<div class="code-panel active" data-group="intro" data-lang="cURL">
<span class="cm"># Listar seus servidores VPS</span>
curl -H <span class="str">"X-API-Key: lrv_live_sua_chave"</span> \
     <?= $baseUrl ?>/api/v1/hosting
</div>
<div class="code-panel" data-group="intro" data-lang="PHP">
<span class="cm">// PHP com cURL</span>
<span class="kw">$ch</span> = curl_init(<span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>);
curl_setopt_array(<span class="kw">$ch</span>, [
    CURLOPT_HTTPHEADER => [<span class="str">'X-API-Key: lrv_live_sua_chave'</span>],
    CURLOPT_RETURNTRANSFER => <span class="kw">true</span>,
]);
<span class="kw">$response</span> = json_decode(curl_exec(<span class="kw">$ch</span>), <span class="kw">true</span>);
curl_close(<span class="kw">$ch</span>);

<span class="fn">print_r</span>(<span class="kw">$response</span>[<span class="str">'data'</span>]);
</div>
<div class="code-panel" data-group="intro" data-lang="JavaScript">
<span class="cm">// JavaScript (fetch API)</span>
<span class="kw">const</span> response = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>, {
    headers: { <span class="str">'X-API-Key'</span>: <span class="str">'lrv_live_sua_chave'</span> }
});
<span class="kw">const</span> { data } = <span class="kw">await</span> response.<span class="fn">json</span>();
console.<span class="fn">log</span>(data);
</div>
<div class="code-panel" data-group="intro" data-lang="Python">
<span class="cm"># Python com requests</span>
<span class="kw">import</span> requests

response = requests.<span class="fn">get</span>(
    <span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>,
    headers={<span class="str">'X-API-Key'</span>: <span class="str">'lrv_live_sua_chave'</span>}
)
data = response.<span class="fn">json</span>()[<span class="str">'data'</span>]
<span class="fn">print</span>(data)
</div>
</div>
</section>

<!-- AUTENTICAÇÃO -->
<section class="doc-section" id="autenticacao">
<h2><?= $t('api_docs.auth_titulo') ?></h2>
<p><?= $t('api_docs.auth_desc') ?></p>

<h3>1. API Key (recomendado para servidores)</h3>
<p>Envie sua chave no header <code>X-API-Key</code> em cada requisição. Simples e direto.</p>

<div class="code-tabs">
<div class="code-tabs-nav">
<button class="active" onclick="showTab(this,'auth1')">cURL</button>
<button onclick="showTab(this,'auth1')">PHP</button>
<button onclick="showTab(this,'auth1')">JavaScript</button>
<button onclick="showTab(this,'auth1')">Python</button>
</div>
<div class="code-panel active" data-group="auth1" data-lang="cURL">
curl -H <span class="str">"X-API-Key: lrv_live_abc123..."</span> \
     <?= $baseUrl ?>/api/v1/tickets
</div>
<div class="code-panel" data-group="auth1" data-lang="PHP">
<span class="kw">$headers</span> = [<span class="str">'X-API-Key: lrv_live_abc123...'</span>];

<span class="kw">$ch</span> = curl_init(<span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>);
curl_setopt(<span class="kw">$ch</span>, CURLOPT_HTTPHEADER, <span class="kw">$headers</span>);
curl_setopt(<span class="kw">$ch</span>, CURLOPT_RETURNTRANSFER, <span class="kw">true</span>);
<span class="kw">$result</span> = json_decode(curl_exec(<span class="kw">$ch</span>), <span class="kw">true</span>);
</div>
<div class="code-panel" data-group="auth1" data-lang="JavaScript">
<span class="kw">const</span> res = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>, {
    headers: { <span class="str">'X-API-Key'</span>: <span class="str">'lrv_live_abc123...'</span> }
});
</div>
<div class="code-panel" data-group="auth1" data-lang="Python">
<span class="kw">import</span> requests
res = requests.<span class="fn">get</span>(
    <span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>,
    headers={<span class="str">'X-API-Key'</span>: <span class="str">'lrv_live_abc123...'</span>}
)
</div>
</div>

<h3>2. Bearer Token (recomendado para apps client-side)</h3>
<p>Troque sua API Key por um token de curta duração (1h). Ideal para aplicações que não devem expor a API Key diretamente.</p>

<div class="code-tabs">
<div class="code-tabs-nav">
<button class="active" onclick="showTab(this,'auth2')">cURL</button>
<button onclick="showTab(this,'auth2')">PHP</button>
<button onclick="showTab(this,'auth2')">JavaScript</button>
<button onclick="showTab(this,'auth2')">Python</button>
</div>
<div class="code-panel active" data-group="auth2" data-lang="cURL">
<span class="cm"># Passo 1: Obter token</span>
curl -X POST <?= $baseUrl ?>/api/v1/auth/token \
     -H <span class="str">"Content-Type: application/json"</span> \
     -d <span class="str">'{"api_key": "lrv_live_abc123..."}'</span>

<span class="cm"># Resposta:</span>
<span class="cm"># {"success":true,"data":{"access_token":"eyJ...","refresh_token":"rft...","expires_in":3600}}</span>

<span class="cm"># Passo 2: Usar o token</span>
curl -H <span class="str">"Authorization: Bearer eyJ..."</span> \
     <?= $baseUrl ?>/api/v1/hosting

<span class="cm"># Passo 3: Renovar quando expirar</span>
curl -X POST <?= $baseUrl ?>/api/v1/auth/refresh \
     -H <span class="str">"Content-Type: application/json"</span> \
     -d <span class="str">'{"refresh_token": "rft..."}'</span>
</div>
<div class="code-panel" data-group="auth2" data-lang="PHP">
<span class="cm">// Passo 1: Obter token</span>
<span class="kw">$ch</span> = curl_init(<span class="str">'<?= $baseUrl ?>/api/v1/auth/token'</span>);
curl_setopt_array(<span class="kw">$ch</span>, [
    CURLOPT_POST => <span class="kw">true</span>,
    CURLOPT_HTTPHEADER => [<span class="str">'Content-Type: application/json'</span>],
    CURLOPT_POSTFIELDS => json_encode([<span class="str">'api_key'</span> => <span class="str">'lrv_live_abc123...'</span>]),
    CURLOPT_RETURNTRANSFER => <span class="kw">true</span>,
]);
<span class="kw">$tokens</span> = json_decode(curl_exec(<span class="kw">$ch</span>), <span class="kw">true</span>)[<span class="str">'data'</span>];

<span class="cm">// Passo 2: Usar o token</span>
<span class="kw">$ch</span> = curl_init(<span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>);
curl_setopt(<span class="kw">$ch</span>, CURLOPT_HTTPHEADER, [<span class="str">'Authorization: Bearer '</span> . <span class="kw">$tokens</span>[<span class="str">'access_token'</span>]]);
</div>
<div class="code-panel" data-group="auth2" data-lang="JavaScript">
<span class="cm">// Passo 1: Obter token</span>
<span class="kw">const</span> { data: tokens } = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'<?= $baseUrl ?>/api/v1/auth/token'</span>, {
    method: <span class="str">'POST'</span>,
    headers: { <span class="str">'Content-Type'</span>: <span class="str">'application/json'</span> },
    body: JSON.<span class="fn">stringify</span>({ api_key: <span class="str">'lrv_live_abc123...'</span> })
}).<span class="fn">then</span>(r => r.<span class="fn">json</span>());

<span class="cm">// Passo 2: Usar o token</span>
<span class="kw">const</span> vps = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>, {
    headers: { <span class="str">'Authorization'</span>: <span class="str">`Bearer ${tokens.access_token}`</span> }
}).<span class="fn">then</span>(r => r.<span class="fn">json</span>());
</div>
<div class="code-panel" data-group="auth2" data-lang="Python">
<span class="kw">import</span> requests

<span class="cm"># Passo 1: Obter token</span>
tokens = requests.<span class="fn">post</span>(
    <span class="str">'<?= $baseUrl ?>/api/v1/auth/token'</span>,
    json={<span class="str">'api_key'</span>: <span class="str">'lrv_live_abc123...'</span>}
).<span class="fn">json</span>()[<span class="str">'data'</span>]

<span class="cm"># Passo 2: Usar o token</span>
vps = requests.<span class="fn">get</span>(
    <span class="str">'<?= $baseUrl ?>/api/v1/hosting'</span>,
    headers={<span class="str">'Authorization'</span>: f<span class="str">'Bearer {tokens["access_token"]}'</span>}
).<span class="fn">json</span>()
</div>
</div>

<h3>Escopos (Permissions)</h3>
<p>Cada API Key tem escopos que definem o que ela pode acessar. Se tentar acessar um recurso sem o escopo necessário, receberá HTTP 403.</p>
<p>Escopos disponíveis: <code>hosting.read</code> <code>hosting.write</code> <code>tickets.read</code> <code>tickets.write</code> <code>domains.read</code> <code>domains.write</code> <code>billing.read</code> <code>billing.write</code> <code>backups.read</code> <code>backups.write</code> <code>monitoring.read</code> <code>databases.read</code> <code>databases.write</code> <code>applications.read</code> <code>applications.write</code> <code>emails.read</code> <code>emails.write</code> <code>webhooks.read</code> <code>webhooks.write</code></p>
</section>

<!-- PRIMEIROS PASSOS -->
<section class="doc-section" id="primeiros-passos">
<h2>Primeiros Passos</h2>
<p>Integrar com a API leva menos de 5 minutos:</p>
<ol>
<li><strong>Crie uma API Key</strong> no painel: <a href="/cliente/api-keys" style="color:#4F46E5">Painel → API Keys → Criar</a></li>
<li><strong>Escolha os escopos</strong> que sua integração precisa (ex: <code>hosting.read</code>, <code>tickets.write</code>)</li>
<li><strong>Faça sua primeira chamada</strong> usando o exemplo abaixo</li>
<li><strong>Teste no Sandbox</strong> primeiro com chave <code>lrv_test_</code> (writes são simulados, reads retornam dados reais)</li>
<li><strong>Troque para Production</strong> mudando para chave <code>lrv_live_</code></li>
</ol>

<h3>Exemplo completo: Criar um ticket via API</h3>
<div class="code-tabs">
<div class="code-tabs-nav">
<button class="active" onclick="showTab(this,'quickstart')">cURL</button>
<button onclick="showTab(this,'quickstart')">PHP</button>
<button onclick="showTab(this,'quickstart')">JavaScript</button>
<button onclick="showTab(this,'quickstart')">Python</button>
</div>
<div class="code-panel active" data-group="quickstart" data-lang="cURL">
curl -X POST <?= $baseUrl ?>/api/v1/tickets \
     -H <span class="str">"X-API-Key: lrv_live_sua_chave"</span> \
     -H <span class="str">"Content-Type: application/json"</span> \
     -d <span class="str">'{
       "subject": "Problema no servidor",
       "message": "O site está lento desde as 14h.",
       "priority": "high",
       "department": "suporte"
     }'</span>

<span class="cm"># Resposta (201 Created):</span>
{
  <span class="str">"success"</span>: <span class="kw">true</span>,
  <span class="str">"message"</span>: <span class="str">"Ticket created successfully."</span>,
  <span class="str">"data"</span>: {
    <span class="str">"id"</span>: <span class="num">42</span>,
    <span class="str">"subject"</span>: <span class="str">"Problema no servidor"</span>,
    <span class="str">"status"</span>: <span class="str">"open"</span>,
    <span class="str">"priority"</span>: <span class="str">"high"</span>
  }
}
</div>
<div class="code-panel" data-group="quickstart" data-lang="PHP">
<span class="kw">$apiKey</span> = <span class="str">'lrv_live_sua_chave'</span>;

<span class="kw">$ch</span> = curl_init(<span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>);
curl_setopt_array(<span class="kw">$ch</span>, [
    CURLOPT_POST => <span class="kw">true</span>,
    CURLOPT_HTTPHEADER => [
        <span class="str">'X-API-Key: '</span> . <span class="kw">$apiKey</span>,
        <span class="str">'Content-Type: application/json'</span>,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        <span class="str">'subject'</span>  => <span class="str">'Problema no servidor'</span>,
        <span class="str">'message'</span>  => <span class="str">'O site está lento desde as 14h.'</span>,
        <span class="str">'priority'</span> => <span class="str">'high'</span>,
        <span class="str">'department'</span> => <span class="str">'suporte'</span>,
    ]),
    CURLOPT_RETURNTRANSFER => <span class="kw">true</span>,
]);

<span class="kw">$response</span> = json_decode(curl_exec(<span class="kw">$ch</span>), <span class="kw">true</span>);
curl_close(<span class="kw">$ch</span>);

<span class="kw">if</span> (<span class="kw">$response</span>[<span class="str">'success'</span>]) {
    echo <span class="str">"Ticket #"</span> . <span class="kw">$response</span>[<span class="str">'data'</span>][<span class="str">'id'</span>] . <span class="str">" criado!"</span>;
}
</div>
<div class="code-panel" data-group="quickstart" data-lang="JavaScript">
<span class="kw">const</span> apiKey = <span class="str">'lrv_live_sua_chave'</span>;

<span class="kw">const</span> response = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>, {
    method: <span class="str">'POST'</span>,
    headers: {
        <span class="str">'X-API-Key'</span>: apiKey,
        <span class="str">'Content-Type'</span>: <span class="str">'application/json'</span>,
    },
    body: JSON.<span class="fn">stringify</span>({
        subject: <span class="str">'Problema no servidor'</span>,
        message: <span class="str">'O site está lento desde as 14h.'</span>,
        priority: <span class="str">'high'</span>,
        department: <span class="str">'suporte'</span>,
    }),
});

<span class="kw">const</span> { success, data } = <span class="kw">await</span> response.<span class="fn">json</span>();
<span class="kw">if</span> (success) {
    console.<span class="fn">log</span>(<span class="str">`Ticket #${data.id} criado!`</span>);
}
</div>
<div class="code-panel" data-group="quickstart" data-lang="Python">
<span class="kw">import</span> requests

api_key = <span class="str">'lrv_live_sua_chave'</span>

response = requests.<span class="fn">post</span>(
    <span class="str">'<?= $baseUrl ?>/api/v1/tickets'</span>,
    headers={<span class="str">'X-API-Key'</span>: api_key},
    json={
        <span class="str">'subject'</span>: <span class="str">'Problema no servidor'</span>,
        <span class="str">'message'</span>: <span class="str">'O site está lento desde as 14h.'</span>,
        <span class="str">'priority'</span>: <span class="str">'high'</span>,
        <span class="str">'department'</span>: <span class="str">'suporte'</span>,
    }
)

result = response.<span class="fn">json</span>()
<span class="kw">if</span> result[<span class="str">'success'</span>]:
    <span class="fn">print</span>(f<span class="str">"Ticket #{result['data']['id']} criado!"</span>)
</div>
</div>
</section>

<!-- ENDPOINTS -->
<section class="doc-section" id="endpoints">
<h2><?= $t('api_docs.endpoints_titulo') ?></h2>
<p>Todos os endpoints seguem REST. Recursos disponíveis:</p>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;margin:20px 0">
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Hosting (VPS)</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>hosting.read</code> / <code>hosting.write</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/hosting<br>
<code style="color:#166534">GET</code> /api/v1/hosting/show?id=<br>
<code style="color:#1e40af">POST</code> /api/v1/hosting/restart?id=<br>
<code style="color:#166534">GET</code> /api/v1/hosting/metrics?id=
</div></div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Tickets</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>tickets.read</code> / <code>tickets.write</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/tickets<br>
<code style="color:#166534">GET</code> /api/v1/tickets/show?id=<br>
<code style="color:#1e40af">POST</code> /api/v1/tickets<br>
<code style="color:#1e40af">POST</code> /api/v1/tickets/reply<br>
<code style="color:#1e40af">POST</code> /api/v1/tickets/close?id=
</div></div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Domínios</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>domains.read</code> / <code>domains.write</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/domains<br>
<code style="color:#166534">GET</code> /api/v1/domains/show?id=<br>
<code style="color:#1e40af">POST</code> /api/v1/domains<br>
<code style="color:#1e40af">POST</code> /api/v1/domains/remove?id=
</div></div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Assinaturas</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>billing.read</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/subscriptions<br>
<code style="color:#166534">GET</code> /api/v1/subscriptions/show?id=<br>
<code style="color:#166534">GET</code> /api/v1/subscriptions/invoices?subscription_id=
</div></div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Bancos de Dados</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>databases.read</code> / <code>databases.write</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/databases<br>
<code style="color:#1e40af">POST</code> /api/v1/databases<br>
<code style="color:#1e40af">POST</code> /api/v1/databases/remove?id=
</div></div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px">
<h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px">Backups</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">Escopo: <code>backups.read</code> / <code>backups.write</code></p>
<div style="font-size:12px;color:#475569;line-height:2">
<code style="color:#166534">GET</code> /api/v1/backups<br>
<code style="color:#1e40af">POST</code> /api/v1/backups<br>
<code style="color:#1e40af">POST</code> /api/v1/backups/restore
</div></div>
</div>

<p style="margin-top:16px"><a href="/developers/api/swagger" style="color:#4F46E5;font-weight:600"><?= $t('api_docs.ver_todos_endpoints') ?></a></p>
</section>

<!-- PAGINAÇÃO -->
<section class="doc-section" id="paginacao">
<h2>Paginação</h2>
<p>Endpoints de listagem retornam dados paginados. Use <code>?page=</code> e <code>?per_page=</code> (máx 100).</p>

<h3>Parâmetros</h3>
<ul>
<li><code>page</code> — Número da página (padrão: 1)</li>
<li><code>per_page</code> — Itens por página (padrão: 25, máx: 100)</li>
<li><code>search</code> — Busca textual (quando disponível)</li>
<li><code>status</code> — Filtrar por status</li>
<li><code>sort</code> — Ordenação: <code>asc</code> ou <code>desc</code></li>
</ul>

<h3>Resposta paginada</h3>
<div class="code-tabs">
<div class="code-tabs-nav"><button class="active" onclick="showTab(this,'pag')">Resposta JSON</button></div>
<div class="code-panel active" data-group="pag" data-lang="Resposta JSON">
{
  <span class="str">"success"</span>: <span class="kw">true</span>,
  <span class="str">"data"</span>: [
    { <span class="str">"id"</span>: <span class="num">1</span>, <span class="str">"hostname"</span>: <span class="str">"vps-01.lrv.cloud"</span>, ... },
    { <span class="str">"id"</span>: <span class="num">2</span>, <span class="str">"hostname"</span>: <span class="str">"vps-02.lrv.cloud"</span>, ... }
  ],
  <span class="str">"meta"</span>: {
    <span class="str">"current_page"</span>: <span class="num">1</span>,
    <span class="str">"per_page"</span>: <span class="num">25</span>,
    <span class="str">"total"</span>: <span class="num">48</span>,
    <span class="str">"last_page"</span>: <span class="num">2</span>
  },
  <span class="str">"links"</span>: {
    <span class="str">"self"</span>: <span class="str">"/api/v1/hosting?page=1&per_page=25"</span>,
    <span class="str">"first"</span>: <span class="str">"/api/v1/hosting?page=1&per_page=25"</span>,
    <span class="str">"last"</span>: <span class="str">"/api/v1/hosting?page=2&per_page=25"</span>,
    <span class="str">"next"</span>: <span class="str">"/api/v1/hosting?page=2&per_page=25"</span>
  }
}
</div>
</div>
</section>

<!-- RATE LIMIT -->
<section class="doc-section" id="rate-limit">
<h2><?= $t('api_docs.rate_titulo') ?></h2>
<p><?= $t('api_docs.rate_desc') ?></p>

<h3>Headers de Rate Limit</h3>
<div class="code-tabs">
<div class="code-tabs-nav"><button class="active" onclick="showTab(this,'rate')">Response Headers</button></div>
<div class="code-panel active" data-group="rate" data-lang="Response Headers">
X-RateLimit-Limit: <span class="num">60</span>        <span class="cm">← Requisições permitidas por janela</span>
X-RateLimit-Remaining: <span class="num">58</span>    <span class="cm">← Requisições restantes</span>
X-RateLimit-Reset: <span class="num">1720540860</span> <span class="cm">← Timestamp (epoch) de reset</span>
</div>
</div>

<h3>Quando exceder o limite (HTTP 429)</h3>
<div class="code-tabs">
<div class="code-tabs-nav"><button class="active" onclick="showTab(this,'rate2')">Resposta 429</button></div>
<div class="code-panel active" data-group="rate2" data-lang="Resposta 429">
<span class="cm">HTTP/1.1 429 Too Many Requests</span>
<span class="cm">Retry-After: 42</span>

{
  <span class="str">"success"</span>: <span class="kw">false</span>,
  <span class="str">"error"</span>: {
    <span class="str">"code"</span>: <span class="str">"RATE_LIMIT_EXCEEDED"</span>,
    <span class="str">"message"</span>: <span class="str">"Too many requests. Please retry after 42 seconds."</span>
  }
}
</div>
</div>
</section>

<!-- WEBHOOKS -->
<section class="doc-section" id="webhooks">
<h2><?= $t('api_docs.webhooks_titulo') ?></h2>
<p><?= $t('api_docs.webhooks_desc') ?></p>

<h3>Eventos disponíveis</h3>
<p><code>ticket.created</code> <code>ticket.replied</code> <code>ticket.closed</code> <code>hosting.created</code> <code>hosting.suspended</code> <code>hosting.restarted</code> <code>payment.received</code> <code>payment.overdue</code> <code>subscription.created</code> <code>subscription.cancelled</code> <code>backup.created</code> <code>domain.added</code> <code>application.installed</code> <code>monitoring.alert</code></p>

<h3>Payload recebido</h3>
<div class="code-tabs">
<div class="code-tabs-nav"><button class="active" onclick="showTab(this,'wh1')">Payload</button></div>
<div class="code-panel active" data-group="wh1" data-lang="Payload">
<span class="cm">POST https://seu-servidor.com/webhook</span>
<span class="cm">Headers:</span>
  Content-Type: application/json
  X-Webhook-Event: ticket.created
  X-Webhook-Signature: sha256=a1b2c3d4e5...
  X-Webhook-Timestamp: <span class="num">1720540800</span>

<span class="cm">Body:</span>
{
  <span class="str">"event"</span>: <span class="str">"ticket.created"</span>,
  <span class="str">"timestamp"</span>: <span class="str">"2026-07-09T20:00:00-03:00"</span>,
  <span class="str">"data"</span>: {
    <span class="str">"id"</span>: <span class="num">42</span>,
    <span class="str">"subject"</span>: <span class="str">"Problema no servidor"</span>,
    <span class="str">"status"</span>: <span class="str">"open"</span>,
    <span class="str">"priority"</span>: <span class="str">"high"</span>
  }
}
</div>
</div>

<h3>Validar assinatura (HMAC SHA-256)</h3>
<div class="code-tabs">
<div class="code-tabs-nav">
<button class="active" onclick="showTab(this,'whval')">PHP</button>
<button onclick="showTab(this,'whval')">JavaScript</button>
<button onclick="showTab(this,'whval')">Python</button>
</div>
<div class="code-panel active" data-group="whval" data-lang="PHP">
<span class="kw">$payload</span> = file_get_contents(<span class="str">'php://input'</span>);
<span class="kw">$signature</span> = <span class="kw">$_SERVER</span>[<span class="str">'HTTP_X_WEBHOOK_SIGNATURE'</span>] ?? <span class="str">''</span>;
<span class="kw">$secret</span> = <span class="str">'seu_webhook_secret'</span>;

<span class="kw">$expected</span> = <span class="str">'sha256='</span> . hash_hmac(<span class="str">'sha256'</span>, <span class="kw">$payload</span>, <span class="kw">$secret</span>);
<span class="kw">$valid</span> = hash_equals(<span class="kw">$expected</span>, <span class="kw">$signature</span>);

<span class="kw">if</span> (!<span class="kw">$valid</span>) {
    http_response_code(<span class="num">401</span>);
    exit(<span class="str">'Invalid signature'</span>);
}

<span class="kw">$event</span> = json_decode(<span class="kw">$payload</span>, <span class="kw">true</span>);
<span class="cm">// Processar evento...</span>
</div>
<div class="code-panel" data-group="whval" data-lang="JavaScript">
<span class="kw">import</span> crypto <span class="kw">from</span> <span class="str">'crypto'</span>;

<span class="kw">function</span> <span class="fn">verifyWebhook</span>(payload, signature, secret) {
    <span class="kw">const</span> expected = <span class="str">'sha256='</span> + crypto
        .<span class="fn">createHmac</span>(<span class="str">'sha256'</span>, secret)
        .<span class="fn">update</span>(payload)
        .<span class="fn">digest</span>(<span class="str">'hex'</span>);
    <span class="kw">return</span> crypto.<span class="fn">timingSafeEqual</span>(
        Buffer.<span class="fn">from</span>(expected),
        Buffer.<span class="fn">from</span>(signature)
    );
}

<span class="cm">// No Express:</span>
app.<span class="fn">post</span>(<span class="str">'/webhook'</span>, (req, res) => {
    <span class="kw">const</span> sig = req.headers[<span class="str">'x-webhook-signature'</span>];
    <span class="kw">if</span> (!<span class="fn">verifyWebhook</span>(req.rawBody, sig, <span class="str">'seu_secret'</span>)) {
        <span class="kw">return</span> res.<span class="fn">status</span>(<span class="num">401</span>).<span class="fn">send</span>(<span class="str">'Invalid'</span>);
    }
    <span class="cm">// Processar evento...</span>
});
</div>
<div class="code-panel" data-group="whval" data-lang="Python">
<span class="kw">import</span> hmac, hashlib

<span class="kw">def</span> <span class="fn">verify_webhook</span>(payload: bytes, signature: str, secret: str) -> bool:
    expected = <span class="str">'sha256='</span> + hmac.<span class="fn">new</span>(
        secret.<span class="fn">encode</span>(), payload, hashlib.sha256
    ).<span class="fn">hexdigest</span>()
    <span class="kw">return</span> hmac.<span class="fn">compare_digest</span>(expected, signature)

<span class="cm"># No Flask:</span>
@app.<span class="fn">route</span>(<span class="str">'/webhook'</span>, methods=[<span class="str">'POST'</span>])
<span class="kw">def</span> <span class="fn">webhook</span>():
    sig = request.headers.<span class="fn">get</span>(<span class="str">'X-Webhook-Signature'</span>, <span class="str">''</span>)
    <span class="kw">if not</span> <span class="fn">verify_webhook</span>(request.data, sig, <span class="str">'seu_secret'</span>):
        <span class="kw">return</span> <span class="str">'Invalid'</span>, <span class="num">401</span>
    <span class="cm"># Processar evento...</span>
</div>
</div>
</section>

<!-- ERROS -->
<section class="doc-section" id="erros">
<h2><?= $t('api_docs.erros_titulo') ?></h2>
<p>Todas as respostas de erro seguem o mesmo formato:</p>

<div class="code-tabs">
<div class="code-tabs-nav"><button class="active" onclick="showTab(this,'err')">Formato de Erro</button></div>
<div class="code-panel active" data-group="err" data-lang="Formato de Erro">
{
  <span class="str">"success"</span>: <span class="kw">false</span>,
  <span class="str">"error"</span>: {
    <span class="str">"code"</span>: <span class="str">"VALIDATION_ERROR"</span>,
    <span class="str">"message"</span>: <span class="str">"The given data was invalid."</span>,
    <span class="str">"details"</span>: [
      { <span class="str">"field"</span>: <span class="str">"domain"</span>, <span class="str">"message"</span>: <span class="str">"Invalid domain format."</span> }
    ]
  }
}
</div>
</div>

<h3>Códigos HTTP</h3>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:13px;margin:12px 0">
<thead><tr style="border-bottom:2px solid #e2e8f0;text-align:left">
<th style="padding:8px 12px;color:#64748b;font-weight:600">Código</th>
<th style="padding:8px 12px;color:#64748b;font-weight:600">Significado</th>
<th style="padding:8px 12px;color:#64748b;font-weight:600">Quando ocorre</th>
</tr></thead>
<tbody>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>200</code></td><td style="padding:8px 12px">OK</td><td style="padding:8px 12px;color:#64748b">Requisição processada com sucesso</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>201</code></td><td style="padding:8px 12px">Created</td><td style="padding:8px 12px;color:#64748b">Recurso criado (ticket, domínio, etc.)</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>202</code></td><td style="padding:8px 12px">Accepted</td><td style="padding:8px 12px;color:#64748b">Ação enfileirada (restart, backup)</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>400</code></td><td style="padding:8px 12px">Bad Request</td><td style="padding:8px 12px;color:#64748b">Parâmetros faltando ou inválidos</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>401</code></td><td style="padding:8px 12px">Unauthorized</td><td style="padding:8px 12px;color:#64748b">API Key inválida ou ausente</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>403</code></td><td style="padding:8px 12px">Forbidden</td><td style="padding:8px 12px;color:#64748b">Escopo insuficiente</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>404</code></td><td style="padding:8px 12px">Not Found</td><td style="padding:8px 12px;color:#64748b">Recurso não encontrado</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>409</code></td><td style="padding:8px 12px">Conflict</td><td style="padding:8px 12px;color:#64748b">Recurso já existe ou conflito de estado</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>422</code></td><td style="padding:8px 12px">Validation Error</td><td style="padding:8px 12px;color:#64748b">Dados inválidos (com detalhes por campo)</td></tr>
<tr style="border-bottom:1px solid #f1f5f9"><td style="padding:8px 12px"><code>429</code></td><td style="padding:8px 12px">Rate Limited</td><td style="padding:8px 12px;color:#64748b">Limite de requisições excedido</td></tr>
<tr><td style="padding:8px 12px"><code>500</code></td><td style="padding:8px 12px">Server Error</td><td style="padding:8px 12px;color:#64748b">Erro interno (reporte ao suporte)</td></tr>
</tbody>
</table>
</div>

<h3>Códigos de erro comuns</h3>
<p><code>UNAUTHORIZED</code> <code>FORBIDDEN</code> <code>NOT_FOUND</code> <code>VALIDATION_ERROR</code> <code>RATE_LIMIT_EXCEEDED</code> <code>MISSING_ID</code> <code>DOMAIN_ALREADY_EXISTS</code> <code>VPS_NOT_RUNNING</code> <code>TICKET_CLOSED</code> <code>INVALID_API_KEY</code> <code>INVALID_SCOPES</code> <code>ENVIRONMENT_MISMATCH</code></p>
</section>

<!-- SDKs -->
<section class="doc-section" id="sdks">
<h2><?= $t('api_docs.sdks_titulo') ?></h2>
<p><?= $t('api_docs.sdks_desc') ?></p>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:20px 0">
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;text-align:center">
<div style="font-size:24px;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="32" height="32"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
<h4 style="font-size:14px;font-weight:700;margin-bottom:4px">PHP</h4>
<code style="font-size:11px">composer require lrv/cloud-manager-sdk</code>
</div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;text-align:center">
<div style="font-size:24px;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="32" height="32"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
<h4 style="font-size:14px;font-weight:700;margin-bottom:4px">JavaScript / TypeScript</h4>
<code style="font-size:11px">npm install @lrv/cloud-manager</code>
</div>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;text-align:center">
<div style="font-size:24px;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="32" height="32"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
<h4 style="font-size:14px;font-weight:700;margin-bottom:4px">Python</h4>
<code style="font-size:11px">pip install lrv-cloud-manager</code>
</div>
</div>

<h3>Downloads</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin:16px 0">
<a href="/api/v1/openapi.yaml" download style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> OpenAPI YAML</a>
<a href="/developers/api/postman.json" download style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Postman</a>
<a href="/developers/api/bruno.json" download style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Bruno</a>
<a href="/developers/api/insomnia.json" download style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Insomnia</a>
<a href="/developers/api/swagger" style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> API Explorer</a>
</div>
</section>

</div><!-- /doc-content -->

<script>
function showTab(btn, group) {
    const nav = btn.parentElement;
    const container = nav.parentElement;
    const lang = btn.textContent.trim();
    nav.querySelectorAll('button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    container.querySelectorAll('.code-panel').forEach(p => {
        p.classList.toggle('active', p.dataset.lang === lang);
    });
}
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
