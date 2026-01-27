<?php
$csrf = zid_ui_csrf_token();
$admin = zid_ui_is_admin();

ob_start();
?>
<section class="hero">
  <div class="hero-header">
    <div>
      <h1>Dashboard ZID UI</h1>
      <p>Visao geral com widgets em tempo real.</p>
    </div>
    <div class="hero-actions">
      <?php if ($admin): ?>
        <button class="btn btn-primary" id="btn-update" type="button">Update</button>
      <?php else: ?>
        <button class="btn btn-primary" id="btn-update" type="button" disabled>Update</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="card" id="kpi-cpu">
      <div class="card-title">CPU</div>
      <div class="card-value">--</div>
      <div class="card-meta">aguardando</div>
    </div>
    <div class="card" id="kpi-mem">
      <div class="card-title">Memoria</div>
      <div class="card-value">--</div>
      <div class="card-meta">aguardando</div>
    </div>
    <div class="card" id="kpi-disk">
      <div class="card-title">Disco</div>
      <div class="card-value">--</div>
      <div class="card-meta">aguardando</div>
    </div>
    <div class="card" id="kpi-health">
      <div class="card-title">Health</div>
      <div class="card-value">--</div>
      <div class="card-meta">aguardando</div>
    </div>
  </div>
</section>

<section class="panel-grid">
  <div class="panel" id="panel-widgets">
    <div class="panel-header">
      <h2>Widgets</h2>
      <span class="panel-meta">Atualizacao automatica</span>
    </div>
    <div class="widgets-grid">
      <div class="widget" id="widget-health">
        <div class="widget-title">Health</div>
        <div class="widget-body">Aguardando dados...</div>
        <div class="widget-footer">last: --</div>
      </div>
      <div class="widget" id="widget-metrics">
        <div class="widget-title">Metrics</div>
        <div class="widget-body">Aguardando dados...</div>
        <div class="widget-footer">last: --</div>
      </div>
      <div class="widget" id="widget-log">
        <div class="widget-title">Log Tail</div>
        <div class="widget-body">Aguardando dados...</div>
        <div class="widget-footer">last: --</div>
      </div>
      <div class="widget" id="widget-map">
        <div class="widget-title">Map Events</div>
        <div class="widget-body">Aguardando dados...</div>
        <div class="widget-footer">last: --</div>
      </div>
    </div>
  </div>

  <div class="panel" id="panel-map">
    <div class="panel-header">
      <h2>Mapa</h2>
      <span class="panel-meta">Leaflet local</span>
    </div>
    <div id="map"></div>
  </div>
</section>

<?php if ($admin): ?>
<section class="panel" id="panel-update">
  <div class="panel-header">
    <h2>Update</h2>
    <span class="panel-meta">Admin only</span>
  </div>
  <pre id="update-output">Pronto para atualizar.</pre>
</section>
<?php endif; ?>

<script>
  window.ZID_UI = window.ZID_UI || {};
  window.ZID_UI.isAdmin = <?php echo $admin ? 'true' : 'false'; ?>;
  window.ZID_UI.csrfToken = <?php echo json_encode($csrf); ?>;
</script>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Dashboard', $content);
