<?php
function zid_ui_render_layout($title, $content_html, $options = array()) {
    $csrf = zid_ui_csrf_token();
    $page_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $show_topbar = !isset($options['show_topbar']) || $options['show_topbar'];
    $show_sidebar = !isset($options['show_sidebar']) || $options['show_sidebar'];
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $layout_class = $show_sidebar ? 'layout' : 'layout layout-full';
    ?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $page_title; ?></title>
  <link rel="stylesheet" href="/assets/css/app.css" />
  <link rel="stylesheet" href="/assets/js/vendor/leaflet/leaflet.css" />
</head>
<body>
  <div class="app">
    <div class="<?php echo $layout_class; ?>">
      <?php if ($show_sidebar): ?>
        <aside class="sidebar">
          <div class="sidebar-brand">
            <span class="brand-badge">ZID</span>
            <span class="brand-text">Security</span>
          </div>
          <nav class="sidebar-nav">
            <a class="sidebar-link <?php echo ($current_path === '/' || $current_path === '/dashboard') ? 'active' : ''; ?>" href="/dashboard">Dashboard</a>
            <a class="sidebar-link <?php echo ($current_path === '/services') ? 'active' : ''; ?>" href="/services">Servicos</a>
            <a class="sidebar-link <?php echo ($current_path === '/firewall') ? 'active' : ''; ?>" href="/firewall">Firewall</a>
            <a class="sidebar-link <?php echo ($current_path === '/vpn') ? 'active' : ''; ?>" href="/vpn">VPN</a>
            <a class="sidebar-link <?php echo ($current_path === '/system_settings') ? 'active' : ''; ?>" href="/system_settings">Sistema</a>
          </nav>
        </aside>
      <?php endif; ?>
      <div class="main">
        <?php if ($show_topbar): ?>
          <header class="topbar">
            <div class="brand">ZID UI</div>
            <div class="meta">UI alternativa</div>
            <div class="topbar-actions">
              <a class="btn btn-outline" href="<?php echo htmlspecialchars(zid_ui_logout_url(), ENT_QUOTES, 'UTF-8'); ?>">Logout</a>
            </div>
          </header>
        <?php endif; ?>
        <main class="content">
          <?php echo $content_html; ?>
        </main>
      </div>
    </div>
  </div>
  <script>
    window.ZID_UI = window.ZID_UI || {};
    window.ZID_UI.csrfToken = <?php echo json_encode($csrf); ?>;
    window.ZID_UI.config = {
      tilesUrl: <?php echo json_encode(zid_ui_get_config('tiles_url')); ?>,
      refreshDefault: <?php echo json_encode(zid_ui_get_config('refresh_default_seconds')); ?>,
      enableSse: <?php echo json_encode(zid_ui_get_config('enable_sse')); ?>
    };
  </script>
  <script src="/assets/js/app.js"></script>
  <script src="/assets/js/widgets.js"></script>
  <script src="/assets/js/vendor/leaflet/leaflet.js"></script>
  <script src="/assets/js/map.js"></script>
</body>
</html>
<?php
}
