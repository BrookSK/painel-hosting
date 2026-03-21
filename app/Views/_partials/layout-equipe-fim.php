<?php declare(strict_types=1); ?>
    </div><!-- /page-content -->
    <?php require __DIR__ . '/footer.php'; ?>
  </div><!-- /app-main -->
</div><!-- /app-shell -->
<script>
(function () {
  var shell = document.getElementById('appShell');
  var toggle = document.getElementById('sidebarToggle');
  var expandBtn = document.getElementById('sidebarExpandBtn');
  var mobileBtn = document.getElementById('mobileMenuBtn');
  var overlay = document.getElementById('sidebarOverlay');
  var sidebar = document.getElementById('sidebar');
  if (localStorage.getItem('lrv_sidebar_collapsed') === '1') shell.classList.add('collapsed');
  function toggleSidebar() {
    shell.classList.toggle('collapsed');
    localStorage.setItem('lrv_sidebar_collapsed', shell.classList.contains('collapsed') ? '1' : '0');
  }
  if (toggle) toggle.addEventListener('click', toggleSidebar);
  if (expandBtn) expandBtn.addEventListener('click', toggleSidebar);
  function openMobile() { sidebar.classList.add('mobile-open'); overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
  function closeMobile() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('active'); document.body.style.overflow = ''; }
  if (mobileBtn) mobileBtn.addEventListener('click', openMobile);
  if (overlay) overlay.addEventListener('click', closeMobile);
  var avatarWrap = document.getElementById('avatarMenu');
  var avatarDrop = document.getElementById('avatarDropdown');
  if (avatarWrap && avatarDrop) {
    avatarWrap.addEventListener('click', function (e) { e.stopPropagation(); avatarDrop.classList.toggle('open'); });
    document.addEventListener('click', function () { avatarDrop.classList.remove('open'); });
  }
})();
</script>
</body>
</html>
