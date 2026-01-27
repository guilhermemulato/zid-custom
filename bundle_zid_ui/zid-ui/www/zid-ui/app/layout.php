<?php
function zid_ui_render_layout($title, $content_html) {
    $csrf = zid_ui_csrf_token();
    $page_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    ?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $page_title; ?></title>
  <link rel="stylesheet" href="/assets/css/app.css" />
</head>
<body>
  <div class="app">
    <header class="topbar">
      <div class="brand">ZID UI</div>
      <div class="meta">PFsense UI alternativa</div>
    </header>
    <main class="content">
      <?php echo $content_html; ?>
    </main>
  </div>
  <script>
    window.ZID_UI = window.ZID_UI || {};
    window.ZID_UI.csrfToken = <?php echo json_encode($csrf); ?>;
  </script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
<?php
}
