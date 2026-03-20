<?php declare(strict_types=1); ?>
<style>
*{box-sizing:border-box;}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu;background:#f8fafc;margin:0;color:#0f172a;}
.topo{background:linear-gradient(90deg,#0B1C3D,#4F46E5,#7C3AED);color:#fff;padding:16px 18px;}
.topo-inner{max-width:1100px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;}
.topo-titulo{font-size:18px;font-weight:700;}
.topo-sub{opacity:.85;font-size:13px;margin-top:2px;}
.nav{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.nav a{color:#fff;text-decoration:none;font-size:13px;padding:5px 10px;border-radius:8px;transition:background .15s;}
.nav a:hover{background:rgba(255,255,255,.15);}
.conteudo{max-width:1100px;margin:0 auto;padding:24px 18px;}
.card{border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 4px 16px rgba(15,23,42,.06);background:#fff;margin-bottom:16px;}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
.linha{display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
.badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;background:#eef2ff;color:#1E3A8A;font-size:12px;font-weight:500;}
.badge-verde{background:#dcfce7;color:#166534;}
.badge-vermelho{background:#fee2e2;color:#991b1b;}
.badge-amarelo{background:#fef3c7;color:#92400e;}
.badge-cinza{background:#f1f5f9;color:#475569;}
a{color:#4F46E5;text-decoration:none;}
a:hover{text-decoration:underline;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;}
.grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;}
.titulo{font-size:18px;font-weight:700;margin:0 0 12px 0;color:#0f172a;}
.subtitulo{font-size:14px;font-weight:600;color:#334155;margin:0 0 8px 0;}
.texto{color:#475569;margin:0 0 10px 0;line-height:1.6;font-size:14px;}
.input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;outline:none;background:#fff;transition:border-color .15s,box-shadow .15s;}
.input:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.botao{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border:0;color:#fff;font-weight:600;font-size:14px;cursor:pointer;transition:opacity .15s,transform .1s;}
.botao:hover{opacity:.9;transform:translateY(-1px);}
.botao:active{transform:translateY(0);}
.botao.sec{background:#0B1C3D;}
.botao.danger{background:linear-gradient(135deg,#dc2626,#b91c1c);}
.botao.sm{padding:7px 12px;font-size:13px;}
.botao.ghost{background:transparent;color:#4F46E5;border:1.5px solid #4F46E5;}
.botao.ghost:hover{background:#eef2ff;}
.erro{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}
.sucesso{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}
.aviso{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 14px;border-radius:12px;margin-bottom:12px;font-size:14px;}
.topo a{color:#fff;text-decoration:none;}
.topo a:hover{text-decoration:underline;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;padding:10px 12px;border-bottom:2px solid #e2e8f0;font-size:13px;color:#64748b;font-weight:600;}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafafa;}
.stat-card{background:linear-gradient(135deg,#0B1C3D,#1e3a8a);color:#fff;border-radius:16px;padding:20px;display:flex;flex-direction:column;gap:6px;}
.stat-card .stat-val{font-size:28px;font-weight:700;}
.stat-card .stat-label{font-size:13px;opacity:.8;}
.stat-card.verde{background:linear-gradient(135deg,#065f46,#059669);}
.stat-card.roxo{background:linear-gradient(135deg,#4F46E5,#7C3AED);}
.stat-card.laranja{background:linear-gradient(135deg,#92400e,#d97706);}
.loading{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg)}}
.dot-online{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;}
.dot-offline{width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;}
.dot-pending{width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;}
.modal-overlay.ativo{display:flex;}
.modal{background:#fff;border-radius:16px;padding:24px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal-titulo{font-size:16px;font-weight:700;margin-bottom:16px;}
.progress-bar{height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden;}
.progress-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#4F46E5,#7C3AED);transition:width .3s;}
@media(max-width:768px){
  .conteudo{padding:16px 12px;}
  .topo{padding:14px 12px;}
  .nav{gap:6px;}
  .nav a{font-size:12px;padding:4px 8px;}
  .grid{grid-template-columns:1fr;}
  .grid-3{grid-template-columns:1fr 1fr;}
  .card{padding:14px;}
  .stat-card .stat-val{font-size:22px;}
  th,td{padding:8px;}
  .botao{padding:9px 14px;font-size:13px;}
  .titulo{font-size:16px;}
}
@media(max-width:480px){
  .grid-3{grid-template-columns:1fr;}
  .nav a{font-size:11px;padding:3px 6px;}
  .card-header{flex-direction:column;align-items:flex-start;}
}
</style>
