<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_tipo_label = $type === 'ticket' ? 'Ticket #' . $refId : 'Chat ao vivo';
$pageTitle = 'Avaliação — ' . $_tipo_label;
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<div class="page-title">Avalie o atendimento</div>
<div class="page-subtitle"><?php echo View::e($_tipo_label); ?></div>

<div class="card-new" style="max-width:560px;">
<?php if ($jaAvaliou): ?>
  <div class="sucesso">Você já avaliou este atendimento. Obrigado pelo feedback!</div>
<?php else: ?>
  <p class="texto" style="margin-bottom:20px;">Sua opinião nos ajuda a melhorar. Leva menos de 30 segundos.</p>

  <div id="avaliacao-form">
    <div style="margin-bottom:24px;">
      <label style="display:block;font-size:13px;color:#64748b;margin-bottom:10px;">Como você avalia o atendimento?</label>
      <div class="stars-row" id="starsRow" style="display:flex;gap:8px;">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <button type="button"
            class="star-btn"
            data-val="<?php echo $i; ?>"
            title="<?php echo $i; ?> estrela<?php echo $i > 1 ? 's' : ''; ?>"
            style="background:none;border:2px solid #e2e8f0;border-radius:10px;width:52px;height:52px;font-size:22px;cursor:pointer;transition:all .15s;color:#94a3b8;">
            ★
          </button>
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
  (function () {
    var selected = 0;
    var labels = ['', 'Muito ruim', 'Ruim', 'Regular', 'Bom', 'Excelente'];
    var colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
    var btns = document.querySelectorAll('.star-btn');
    var lbl  = document.getElementById('ratingLabel');
    var btn  = document.getElementById('btnEnviar');

    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        selected = parseInt(this.dataset.val);
        btns.forEach(function (x, i) {
          var active = i < selected;
          x.style.background    = active ? colors[selected] : '';
          x.style.borderColor   = active ? colors[selected] : '#e2e8f0';
          x.style.color         = active ? '#fff' : '#94a3b8';
        });
        lbl.textContent = labels[selected];
        lbl.style.color = colors[selected];
        btn.disabled = false;
      });
    });

    btn.addEventListener('click', function () {
      if (selected < 1) return;
      btn.disabled = true;
      btn.textContent = 'Enviando...';

      var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

      fetch('/cliente/avaliar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'x-csrf-token': csrf },
        body: new URLSearchParams({
          type:         '<?php echo View::e($type); ?>',
          reference_id: '<?php echo (int)$refId; ?>',
          rating:       selected,
          comment:      document.getElementById('commentField').value,
          agent_id:     '<?php echo (int)$agentId; ?>',
          _csrf:        csrf
        })
      })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j.ok) {
          document.getElementById('avaliacao-form').innerHTML =
            '<div class="sucesso" style="font-size:15px;">Obrigado pelo feedback! Sua avaliação foi registrada.</div>';
        } else {
          document.getElementById('feedbackMsg').textContent = j.erro || 'Erro ao enviar.';
          document.getElementById('feedbackMsg').style.color = '#ef4444';
          btn.disabled = false;
          btn.textContent = 'Enviar avaliação';
        }
      })
      .catch(function () {
        document.getElementById('feedbackMsg').textContent = 'Erro de rede.';
        document.getElementById('feedbackMsg').style.color = '#ef4444';
        btn.disabled = false;
        btn.textContent = 'Enviar avaliação';
      });
    });
  })();
  </script>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
