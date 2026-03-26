<?php declare(strict_types=1); ?>
<?php require __DIR__ . '/estilo.php'; ?>
<style>
/* ── Reset & Base ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, sans-serif;
  background: #f1f5f9;
  color: #0f172a;
  min-height: 100vh;
  display: flex;
}
a { color: inherit; text-decoration: none; }
a:hover { text-decoration: none; }

/* ── Layout Shell ─────────────────────────────────────────── */
.app-shell {
  display: flex;
  width: 100%;
  min-height: 100vh;
}
.app-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  margin-left: 250px;
  transition: margin-left .25s ease;
}
.app-shell.collapsed .app-main { margin-left: 64px; }

/* ── Sidebar ──────────────────────────────────────────────── */
.sidebar {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: 250px;
  background: #0f172a;
  display: flex;
  flex-direction: column;
  z-index: 200;
  transition: width .25s ease;
  overflow: hidden;
}
.app-shell.collapsed .sidebar { width: 64px; }
.app-shell.collapsed .sidebar-logo { justify-content: center; padding: 18px 0 14px; gap: 0; }
.app-shell.collapsed .sidebar-logo img:not(.sidebar-favicon) { display: none; }
.app-shell.collapsed .sidebar-logo .sidebar-favicon { display: flex !important; width: 32px; height: 32px; }
.app-shell.collapsed .sidebar-logo .sidebar-logo-icon { width: 32px; height: 32px; }
.app-shell.collapsed .sidebar-toggle { display: none; }

/* Logo */
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 16px 14px;
  border-bottom: 1px solid rgba(255,255,255,.07);
  min-height: 64px;
  position: relative;
}
.sidebar-logo-icon {
  flex-shrink: 0;
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
}
.sidebar-logo-text {
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  opacity: 1;
  transition: opacity .2s;
}
.app-shell.collapsed .sidebar-logo-text { opacity: 0; width: 0; overflow: hidden; }

.sidebar-toggle {
  margin-left: auto;
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: color .15s, background .15s;
  flex-shrink: 0;
}
.sidebar-toggle:hover { color: #fff; background: rgba(255,255,255,.08); }
.app-shell.collapsed .sidebar-toggle svg { transform: rotate(180deg); }
.app-shell.collapsed .sidebar-logo { cursor: pointer; }

/* Nav */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 10px 8px;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.1) transparent;
}
.sidebar-section-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: #475569;
  padding: 10px 10px 4px;
  white-space: nowrap;
  overflow: hidden;
  transition: opacity .2s;
}
.app-shell.collapsed .sidebar-section-label { opacity: 0; }

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: background .15s, color .15s;
  white-space: nowrap;
  position: relative;
  margin-bottom: 1px;
}
.nav-item:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
.nav-item.nav-ativo { background: rgba(99,102,241,.18); color: #818cf8; }
.nav-item.nav-ativo .nav-icon { color: #818cf8; }
.nav-item-danger { color: #f87171 !important; }
.nav-item-danger:hover { background: rgba(239,68,68,.12) !important; }

.nav-icon {
  width: 18px; height: 18px;
  flex-shrink: 0;
  color: currentColor;
}
.nav-item span:not(.nav-badge) {
  overflow: hidden;
  transition: opacity .2s, width .2s;
}
.app-shell.collapsed .nav-item span:not(.nav-badge) { opacity: 0; width: 0; }
.app-shell.collapsed .nav-item { justify-content: center; padding: 10px; }

.nav-badge {
  margin-left: auto;
  background: #ef4444;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
  flex-shrink: 0;
}
.app-shell.collapsed .nav-badge { display: none; }

/* Tooltip no modo colapsado */
.app-shell.collapsed .nav-item::after {
  content: attr(data-tooltip);
  position: absolute;
  left: calc(100% + 10px);
  top: 50%;
  transform: translateY(-50%);
  background: #1e293b;
  color: #e2e8f0;
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 6px;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity .15s;
  z-index: 300;
}
.app-shell.collapsed .nav-item:hover::after { opacity: 1; }

/* Sidebar footer */
.sidebar-footer {
  padding: 8px;
  border-top: 1px solid rgba(255,255,255,.07);
}

/* ── Header ───────────────────────────────────────────────── */
.app-header {
  height: 60px;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  gap: 12px;
  position: sticky;
  top: 0;
  z-index: 100;
}
.header-left { display: flex; align-items: center; gap: 12px; flex: 1; }
.header-right { display: flex; align-items: center; gap: 8px; }

/* Lang dropdown — variante clara para header branco */
.app-header .lang-trigger {
  background: #f1f5f9;
  border-color: #e2e8f0;
  color: #334155;
}
.app-header .lang-trigger:hover { background: #e2e8f0; border-color: #cbd5e1; }
.app-header .lang-chevron { color: #64748b; opacity: 1; }
.app-header .lang-menu { background: #fff; border-color: #e2e8f0; box-shadow: 0 8px 32px rgba(15,23,42,.12); }
.app-header .lang-option { color: #475569; }
.app-header .lang-option:hover { background: #f8fafc; color: #0f172a; }
.app-header .lang-option-ativo { color: #4F46E5; }
.app-header .lang-check { color: #4F46E5; }

.header-menu-btn {
  display: none;
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
}
.header-menu-btn:hover { background: #f1f5f9; color: #0f172a; }

.header-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 7px 12px;
  max-width: 320px;
  width: 100%;
  flex-shrink: 1;
}
.header-search-input {
  border: none;
  background: none;
  outline: none;
  font-size: 13px;
  color: #0f172a;
  width: 100%;
}
.header-search-input::placeholder { color: #94a3b8; }

.header-icon-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px; height: 36px;
  border-radius: 8px;
  color: #64748b;
  transition: background .15s, color .15s;
}
.header-icon-btn:hover { background: #f1f5f9; color: #0f172a; }
.header-badge {
  position: absolute;
  top: 4px; right: 4px;
  background: #ef4444;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  width: 16px; height: 16px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

.header-avatar-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 10px;
  border-radius: 10px;
  cursor: pointer;
  position: relative;
  transition: background .15s;
}
.header-avatar-wrap:hover { background: #f1f5f9; }
.header-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4F46E5, #7C3AED);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.header-avatar-info { display: flex; flex-direction: column; }
.header-avatar-name { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.2; }
.header-avatar-role { font-size: 11px; color: #94a3b8; line-height: 1.2; }

/* Avatar dropdown */
.avatar-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(15,23,42,.12);
  min-width: 200px;
  z-index: 500;
  overflow: hidden;
}
.avatar-dropdown.open { display: block; }
.avatar-dropdown-info {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
}
.avatar-dropdown-name { font-size: 13px; font-weight: 600; color: #0f172a; }
.avatar-dropdown-email { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.avatar-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 14px;
  font-size: 13px;
  color: #334155;
  transition: background .12s;
}
.avatar-dropdown-item:hover { background: #f8fafc; }
.avatar-dropdown-danger { color: #ef4444 !important; }
.avatar-dropdown-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }

/* ── Page Content ─────────────────────────────────────────── */
.page-content {
  flex: 1;
  padding: 24px;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 4px;
}
.page-subtitle {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 24px;
}

/* ── Stat Cards ───────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}
.stat-card-new {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  transition: box-shadow .15s, transform .15s;
}
.stat-card-new:hover { box-shadow: 0 4px 20px rgba(15,23,42,.08); transform: translateY(-1px); }
.stat-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.stat-card-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.stat-card-icon {
  width: 34px; height: 34px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-card-icon.blue   { background: #eff6ff; color: #3b82f6; }
.stat-card-icon.purple { background: #f5f3ff; color: #7C3AED; }
.stat-card-icon.green  { background: #f0fdf4; color: #16a34a; }
.stat-card-icon.orange { background: #fff7ed; color: #ea580c; }
.stat-card-icon.red    { background: #fef2f2; color: #dc2626; }
.stat-card-icon.indigo { background: #eef2ff; color: #4F46E5; }
.stat-card-value {
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1;
}
.stat-card-value.sm { font-size: 20px; }
.stat-card-sub {
  font-size: 12px;
  color: #94a3b8;
}

/* ── Content Grid ─────────────────────────────────────────── */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}
@media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }

/* ── Card ─────────────────────────────────────────────────── */
.card-new {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
}
.card-new-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.card-new-title svg { color: #4F46E5; }

/* ── Quick Actions ────────────────────────────────────────── */
.quick-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  color: #334155;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: border-color .15s, background .15s, color .15s, box-shadow .15s;
  text-decoration: none;
}
.quick-action-btn:hover {
  border-color: #4F46E5;
  background: #f5f3ff;
  color: #4F46E5;
  box-shadow: 0 2px 8px rgba(79,70,229,.1);
}
.quick-action-icon {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background .15s;
}
.quick-action-btn:hover .quick-action-icon { background: #ede9fe; }

/* ── User Card ────────────────────────────────────────────── */
.user-card-avatar {
  width: 52px; height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4F46E5, #7C3AED);
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 12px;
}
.user-card-name { font-size: 15px; font-weight: 700; color: #0f172a; }
.user-card-email { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.user-card-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 8px;
  padding: 3px 10px;
  border-radius: 999px;
  background: #f5f3ff;
  color: #7C3AED;
  font-size: 11px;
  font-weight: 600;
}
.user-card-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
}
.btn-sm {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .15s;
  text-decoration: none;
}
.btn-sm:hover { opacity: .85; }
.btn-primary { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; border: none; }
.btn-outline { background: #fff; color: #334155; border: 1.5px solid #e2e8f0; }
.btn-outline:hover { border-color: #4F46E5; color: #4F46E5; }

/* ── Badges ───────────────────────────────────────────────── */
.badge-new {
  display: inline-flex; align-items: center;
  padding: 3px 9px; border-radius: 999px;
  font-size: 11px; font-weight: 600;
}
.badge-green, .badge-success  { background: #dcfce7; color: #166534; }
.badge-red, .badge-danger      { background: #fee2e2; color: #991b1b; }
.badge-yellow, .badge-warning  { background: #fef3c7; color: #92400e; }
.badge-blue, .badge-info       { background: #dbeafe; color: #1e40af; }
.badge-purple                  { background: #f5f3ff; color: #6d28d9; }
.badge-gray, .badge-neutral    { background: #f1f5f9; color: #475569; }

/* ── Skeleton ─────────────────────────────────────────────── */
.skeleton {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}
@keyframes shimmer { to { background-position: -200% 0; } }

/* ── Idioma (override) ────────────────────────────────────── */
.idioma-select {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 5px 8px;
  font-size: 12px;
  color: #334155;
  cursor: pointer;
  outline: none;
}

/* Sidebar toggle visível no header quando colapsado */
.sidebar-expand-btn {
  display: none;
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
  align-items: center;
  justify-content: center;
  transition: color .15s, background .15s;
  position: relative;
  z-index: 10;
  pointer-events: all;
  flex-shrink: 0;
}
.sidebar-expand-btn:hover { color: #0f172a; background: #f1f5f9; }
.app-shell.collapsed .sidebar-expand-btn { display: flex; }
.app-shell:not(.collapsed) .sidebar-expand-btn { display: none; }
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); transition: transform .25s ease, width .25s ease; }
  .sidebar.mobile-open { transform: translateX(0); }
  .app-main { margin-left: 0 !important; }
  .header-menu-btn { display: flex; }
  .header-avatar-info { display: none; }
  .header-search { max-width: 180px; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .quick-actions { grid-template-columns: 1fr; }
  .page-content { padding: 16px; }
}
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}

/* ── Overlay mobile ───────────────────────────────────────── */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 199;
}
.sidebar-overlay.active { display: block; }
</style>
