<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$_tipo_label = $type === 'ticket' ? 'Ticket #' . $refId : 'Chat ao vivo';
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
  <title>Avaliação — <?php echo View::e($_tipo_label); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Avaliação</div>
        <div style="opacity:.9;font-size:13px;"><?php echo View::e($_tipo_label); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:560px;margin:0 auto;">
      <?php if ($jaAvaliou): ?>
        <div class="sucesso">Você já avaliou este atendimento. Obrigado pelo feedback!</div>
      <?php else: ?>
        <h1 class="titulo" style="margin-bottom:6px;">Avalie o atendimento</h1>
        <p class="texto" style="margin-bottom:20px;">Sua opinião nos ajuda a melhorar. Leva menos de 30 segundos.</p>

        <div id="avaliacao-form">
          <div style="margin-bottom:24px;">
            <label style="display:block;font-size:13px;color:#64748b;margin-bottom:10px;">Como você avalia o atendimento?</label>
            <div id="starsRow" style="display:flex;gap:8px;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="star-btn" data-val="<?php echo $i; ?>"
                  title="<?php echo $i; ?> estrela<?php echo $i > 1 ? 's' : ''; ?>"
                  style="background:none;border:2px solid #e2e8f0;border-radius:10px;width:52px;height:52px;font-size:22px;cursor:pointer;transition:all .15s;color:#94a3b8;">★</button>
              <?php endfor; ?>
            </div>
            <div id="ratingLabel" style="font-size:13px;color:#64748b;margin-top:8px;min-height:18px;"></div>
          </div>

          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;color:#64748b;margin-bottom:6px;">Comentário (opcional)</label>
            <textarea id="commentField" class="input" rows="3" maxlength="1000" placeholder="Conte o que achou do atendimento..." style="resize:vertical;"></textarea>
          </div>

          <button id="btnEnviar" class="botao" disabled>Enviar avaliação</button>
          <div id="feedbackMsg" style="margin-top:12px;font-size:14px;"></div>
        </div>

        <script>
        (function(){
          var selected=0;
          var labels=['','Muito ruim','Ruim','Regular','Bom','Excelente'];
          var colors=['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
          var btns=document.querySelectorAll('.star-btn');
          var lbl=document.getElementById('ratingLabel');
          var btn=document.getElementById('btnEnviar');
          btns.forEach(function(b){
            b.addEventListener('click',function(){
              selected=parseInt(this.dataset.val);
              btns.forEach(function(x,i){
                var a=i<selected;
                x.style.background=a?colors[selected]:'';
                x.style.borderColor=a?colors[selected]:'#e2e8f0';
                x.style.color=a?'#fff':'#94a3b8';
              });
              lbl.textContent=labels[selected];lbl.style.color=colors[selected];btn.disabled=false;
            });
          });
          btn.addEventListener('click',function(){
            if(selected<1)return;
            btn.disabled=true;btn.textContent='Enviando...';
            var csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
            fetch('/cliente/avaliar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
              body:new URLSearchParams({type:'<?php echo View::e($type); ?>',reference_id:'<?php echo (int)$refId; ?>',
                rating:selected,comment:document.getElementById('commentField').value,
                agent_id:'<?php echo (int)$agentId; ?>',_csrf:csrf})
            }).then(function(r){return r.json();}).then(function(j){
              if(j.ok){document.getElementById('avaliacao-form').innerHTML='<div class="sucesso" style="font-size:15px;">Obrigado! Sua avaliação foi registrada.</div>';}
              else{document.getElementById('feedbackMsg').textContent=j.erro||'Erro ao enviar.';document.getElementById('feedbackMsg').style.color='#ef4444';btn.disabled=false;btn.textContent='Enviar avaliação';}
            }).catch(function(){document.getElementById('feedbackMsg').textContent='Erro de rede.';document.getElementById('feedbackMsg').style.color='#ef4444';btn.disabled=false;btn.textContent='Enviar avaliação';});
          });
        })();
        </script>
      <?php endif; ?>
    </div>
  </div>
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
