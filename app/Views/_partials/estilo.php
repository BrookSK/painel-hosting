<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;
// Só emite favicon se o partial seo.php ainda não foi incluído (ele já cuida disso)
if (!defined('_SEO_PARTIAL_LOADED')):
    $_favicon = SistemaConfig::faviconUrl();
    if ($_favicon !== ''):
?>
<link rel="icon" href="<?php echo View::e($_favicon); ?>" />
<?php
    endif;
endif;
?>
<style>
/* ── Reset & Base ─────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#f1f5f9;color:#0f172a;overflow-x:hidden;}
a{color:#4F46E5;text-decoration:none;}
a:hover{text-decoration:none;}

/* ── Legacy topo (compatibilidade) ───────────────────── */
.topo{background:linear-gradient(90deg,#060d1f,#0B1C3D,#4F46E5,#7C3AED);color:#fff;padding:16px 18px;}
.topo-inner{max-width:1100px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;}
.topo-titulo{font-size:18px;font-weight:700;}
.topo-sub{opacity:.85;font-size:13px;margin-top:2px;}
.nav{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.nav a{color:#fff;text-decoration:none;font-size:13px;padding:5px 10px;border-radius:8px;transition:background .15s;}
.nav a:hover{background:rgba(255,255,255,.15);}
.topo a{color:#fff;text-decoration:none;}
.topo a:hover{text-decoration:underline;}

/* ── Layout ───────────────────────────────────────────── */
.conteudo{max-width:1100px;margin:0 auto;padding:28px 20px;}

/* ── Cards ────────────────────────────────────────────── */
.card{border:1px solid #e2e8f0;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(15,23,42,.05);background:#fff;margin-bottom:16px;}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;}

/* ── Typography ───────────────────────────────────────── */
.titulo{font-size:20px;font-weight:800;margin:0 0 12px;color:#0f172a;letter-spacing:-.02em;}
.subtitulo{font-size:14px;font-weight:600;color:#334155;margin:0 0 8px;}
.texto{color:#475569;margin:0 0 10px;line-height:1.65;font-size:14px;}

/* ── Badges ───────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:#eef2ff;color:#1E3A8A;font-size:12px;font-weight:600;}
.badge-verde,.badge-success,.badge-green{background:#dcfce7;color:#166534;}
.badge-vermelho,.badge-danger,.badge-red{background:#fee2e2;color:#991b1b;}
.badge-amarelo,.badge-warning,.badge-yellow{background:#fef3c7;color:#92400e;}
.badge-cinza,.badge-neutral,.badge-gray{background:#f1f5f9;color:#475569;}
.badge-azul,.badge-info,.badge-blue{background:#dbeafe;color:#1e40af;}
.badge-roxo,.badge-purple{background:#f5f3ff;color:#6d28d9;}

/* ── Inputs ───────────────────────────────────────────── */
.input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;outline:none;background:#fff;transition:border-color .15s,box-shadow .15s;font-family:inherit;color:#0f172a;}
.input:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.input::placeholder{color:#94a3b8;}

/* ── Buttons ──────────────────────────────────────────── */
.botao{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:12px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border:0;color:#fff;font-weight:700;font-size:14px;cursor:pointer;transition:opacity .15s,transform .1s;text-decoration:none;font-family:inherit;}
.botao:hover{opacity:.88;transform:translateY(-1px);text-decoration:none;}
.botao:active{transform:translateY(0);}
.botao.sec{background:linear-gradient(135deg,#0B1C3D,#1e3a8a);}
.botao.danger{background:linear-gradient(135deg,#dc2626,#b91c1c);}
.botao.sm{padding:7px 14px;font-size:13px;border-radius:10px;}
.botao.ghost{background:transparent;color:#4F46E5;border:1.5px solid #4F46E5;}
.botao.ghost:hover{background:#eef2ff;}
.botao.outline-white{background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25);}
.botao.outline-white:hover{background:rgba(255,255,255,.18);}

/* ── Alerts ───────────────────────────────────────────── */
.erro{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}
.sucesso{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}
.aviso{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}

/* ── Grid ─────────────────────────────────────────────── */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;}
.grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;}
.linha{display:flex;gap:12px;flex-wrap:wrap;align-items:center;}

/* ── Tables ───────────────────────────────────────────── */
table{width:100%;border-collapse:collapse;}
th{text-align:left;padding:10px 12px;border-bottom:2px solid #e2e8f0;font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafafa;}

/* ── Stat cards ───────────────────────────────────────── */
.stat-card{background:linear-gradient(135deg,#0B1C3D,#1e3a8a);color:#fff;border-radius:16px;padding:20px;display:flex;flex-direction:column;gap:6px;}
.stat-card .stat-val{font-size:28px;font-weight:800;}
.stat-card .stat-label{font-size:13px;opacity:.75;}
.stat-card.verde{background:linear-gradient(135deg,#065f46,#059669);}
.stat-card.roxo{background:linear-gradient(135deg,#4F46E5,#7C3AED);}
.stat-card.laranja{background:linear-gradient(135deg,#92400e,#d97706);}

/* ── Misc ─────────────────────────────────────────────── */
.loading{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg)}}
.dot-online{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;}
.dot-offline{width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;}
.dot-pending{width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100;align-items:center;justify-content:center;}
.modal-overlay.ativo{display:flex;}
.modal{background:#fff;border-radius:18px;padding:26px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal-titulo{font-size:16px;font-weight:700;margin-bottom:16px;}
.progress-bar{height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden;}
.progress-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#4F46E5,#7C3AED);transition:width .3s;}

/* ── Responsive ───────────────────────────────────────── */
@media(max-width:768px){
  .conteudo{padding:16px 14px;}
  .topo{padding:14px 14px;}
  .nav{gap:6px;}
  .nav a{font-size:12px;padding:4px 8px;}
  .grid{grid-template-columns:1fr;}
  .grid-3{grid-template-columns:1fr 1fr;}
  .card{padding:16px;}
  .stat-card .stat-val{font-size:22px;}
  th,td{padding:8px 10px;}
  .botao{padding:9px 16px;font-size:13px;}
  .titulo{font-size:17px;}
}
@media(max-width:480px){
  .grid-3{grid-template-columns:1fr;}
  .nav a{font-size:11px;padding:3px 6px;}
  .card-header{flex-direction:column;align-items:flex-start;}
}
</style>
<?php
// Widget de suporte flutuante — só em páginas do cliente logado
if (\LRV\Core\Auth::clienteId() !== null) {
    require __DIR__ . '/chat-widget.php';
}
?>
