<?php declare(strict_types=1); ?>
    </div><!-- /page-content -->
  </div><!-- /app-main -->
</div><!-- /app-shell -->
<?php require __DIR__ . '/chat-widget.php'; ?>
<script>
(function () {
  var shell    = document.getElementById('appShell');
  var sidebar  = document.getElementById('sidebar');
  var overlay  = document.getElementById('sidebarOverlay');
  var toggle   = document.getElementById('sidebarToggle');
  var expandBtn= document.getElementById('sidebarExpandBtn');
  var mobileBtn= document.getElementById('mobileMenuBtn');

  if (!shell) return;

  // Restaurar estado salvo
  if (localStorage.getItem('lrv_sidebar_collapsed') === '1') {
    shell.classList.add('collapsed');
  }

  function toggleSidebar() {
    shell.classList.toggle('collapsed');
    localStorage.setItem('lrv_sidebar_collapsed', shell.classList.contains('collapsed') ? '1' : '0');
  }

  if (toggle)    toggle.addEventListener('click',    function(e){ e.stopPropagation(); toggleSidebar(); });
  if (expandBtn) expandBtn.addEventListener('click', function(e){ e.stopPropagation(); toggleSidebar(); });

  // Click on logo area to expand when collapsed
  var logoArea = document.querySelector('.sidebar-logo');
  if (logoArea) {
    logoArea.addEventListener('click', function(e) {
      if (shell.classList.contains('collapsed') && e.target.closest('.sidebar-toggle') === null) {
        e.stopPropagation();
        toggleSidebar();
      }
    });
  }

  // Mobile
  function openMobile()  { if(sidebar) sidebar.classList.add('mobile-open');    if(overlay) overlay.classList.add('active');    document.body.style.overflow='hidden'; }
  function closeMobile() { if(sidebar) sidebar.classList.remove('mobile-open'); if(overlay) overlay.classList.remove('active'); document.body.style.overflow=''; }
  if (mobileBtn) mobileBtn.addEventListener('click', openMobile);
  if (overlay)   overlay.addEventListener('click',   closeMobile);

  // Avatar dropdown
  var avatarWrap = document.getElementById('avatarMenu');
  var avatarDrop = document.getElementById('avatarDropdown');
  if (avatarWrap && avatarDrop) {
    avatarWrap.addEventListener('click', function(e) { e.stopPropagation(); avatarDrop.classList.toggle('open'); });
    document.addEventListener('click',  function()   { avatarDrop.classList.remove('open'); });
  }
})();
</script>
</body>
</html>
