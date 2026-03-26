    </div><!-- /page-content -->
  </div><!-- /app-main -->
</div><!-- /app-shell -->

<?php require __DIR__ . '/chat-widget.php'; ?>

<script>
(function(){
  var s = document.getElementById('appShell');
  var toggle = document.getElementById('sidebarToggle');
  if (s && localStorage.getItem('lrv_cli_sidebar_collapsed') === '1') {
    s.classList.add('collapsed');
  }
  if (toggle && s) {
    toggle.addEventListener('click', function(e) {
      e.stopPropagation();
      s.classList.toggle('collapsed');
      localStorage.setItem('lrv_cli_sidebar_collapsed', s.classList.contains('collapsed') ? '1' : '0');
    });
  }
  // Click on logo area to expand when collapsed
  var logoArea = document.querySelector('.sidebar-logo');
  if (logoArea && s) {
    logoArea.addEventListener('click', function(e) {
      if (s.classList.contains('collapsed') && e.target.closest('.sidebar-toggle') === null) {
        e.stopPropagation();
        s.classList.toggle('collapsed');
        localStorage.setItem('lrv_cli_sidebar_collapsed', s.classList.contains('collapsed') ? '1' : '0');
      }
    });
  }
})();

function abrirSidebarCli() {
  var sb = document.getElementById('sidebar');
  var ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.add('mobile-open');
  if (ov) ov.classList.add('active');
}
function fecharSidebarCli() {
  var sb = document.getElementById('sidebar');
  var ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.remove('mobile-open');
  if (ov) ov.classList.remove('active');
}
function expandirSidebarCli() {
  var s = document.getElementById('appShell');
  if (s) {
    s.classList.remove('collapsed');
    localStorage.setItem('lrv_cli_sidebar_collapsed', '0');
  }
}
function toggleAvatarDropdownCli() {
  var d = document.getElementById('avatarDropdownCli');
  if (d) d.classList.toggle('open');
}
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('avatarWrapCli');
  var drop = document.getElementById('avatarDropdownCli');
  if (drop && wrap && !wrap.contains(e.target)) drop.classList.remove('open');
});
</script>
</body>
</html>
