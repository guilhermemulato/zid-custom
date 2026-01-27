<?php
$interfaces = array('lan' => 'LAN');
if (function_exists('get_configured_interface_with_descr')) {
  $interfaces = get_configured_interface_with_descr();
} elseif (function_exists('get_configured_interface_list')) {
  $interfaces = array();
  foreach (get_configured_interface_list() as $if) {
    $interfaces[$if] = strtoupper($if);
  }
}

ob_start();
?>
<section class="hero">
  <div class="hero-header">
    <div>
      <h1>Servicos</h1>
      <p>Gerencie servicos a partir da ZID UI.</p>
    </div>
    <div class="hero-actions"></div>
  </div>

  <div class="kpi-grid">
    <div class="card" id="kpi-dhcp">
      <div class="card-title">DHCP Server</div>
      <div class="card-value">--</div>
      <div class="card-meta">aguardando</div>
    </div>
  </div>
</section>

<section class="panel-grid">
  <div class="panel" id="panel-services">
    <div class="panel-header">
      <h2>DHCP Server</h2>
      <span class="panel-meta">Status e configuracao</span>
    </div>
    <div class="widget" id="widget-services">
      <div class="widget-title">DHCP</div>
      <div class="widget-body">Aguardando dados...</div>
      <div class="widget-footer">last: --</div>
    </div>
    <div class="panel-actions">
      <button class="btn btn-outline" data-service="dhcpd" data-action="start">Iniciar</button>
      <button class="btn btn-outline" data-service="dhcpd" data-action="stop">Parar</button>
      <button class="btn btn-outline" data-service="dhcpd" data-action="restart">Reiniciar</button>
    </div>
  </div>
  <div class="panel" id="panel-dhcp-config">
    <div class="panel-header">
      <h2>Pool de enderecos</h2>
      <span class="panel-meta">Configuracao essencial</span>
    </div>
    <form class="form-grid" id="dhcp-config-form">
      <label>
        Interface
        <select name="interface">
          <?php foreach ($interfaces as $if => $descr): ?>
            <option value="<?php echo htmlspecialchars($if, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($descr, ENT_QUOTES, 'UTF-8'); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        Range inicial
        <input name="range_from" type="text" placeholder="ex: 192.168.1.100" />
      </label>
      <label>
        Range final
        <input name="range_to" type="text" placeholder="ex: 192.168.1.200" />
      </label>
      <label class="checkbox">
        <input type="checkbox" name="enable" value="1" />
        Habilitar DHCP nesta interface
      </label>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
      <div class="form-status" id="dhcp-config-status">Aguardando...</div>
    </form>
  </div>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Servicos', $content);
