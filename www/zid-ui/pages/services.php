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
      <label>
        Gateway (opcional)
        <input name="gateway" type="text" placeholder="ex: 192.168.1.1" />
      </label>
      <label>
        DNS 1 (opcional)
        <input name="dns1" type="text" placeholder="ex: 1.1.1.1" />
      </label>
      <label>
        DNS 2 (opcional)
        <input name="dns2" type="text" placeholder="ex: 8.8.8.8" />
      </label>
      <label>
        Lease padrao (segundos)
        <input name="default_leasetime" type="text" placeholder="ex: 7200" />
      </label>
      <label>
        Lease maxima (segundos)
        <input name="max_leasetime" type="text" placeholder="ex: 86400" />
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
  <div class="panel" id="panel-dhcp-static">
    <div class="panel-header">
      <h2>Mapeamentos estaticos</h2>
      <span class="panel-meta">DHCP - principais dados</span>
    </div>
    <div class="table-wrap">
      <table class="data-table" id="dhcp-static-table">
        <thead>
          <tr>
            <th>MAC</th>
            <th>IP</th>
            <th>Descricao</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="3">Carregando...</td></tr>
        </tbody>
      </table>
    </div>
    <form class="form-grid" id="dhcp-static-form">
      <label>
        Interface
        <select name="interface">
          <?php foreach ($interfaces as $if => $descr): ?>
            <option value="<?php echo htmlspecialchars($if, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($descr, ENT_QUOTES, 'UTF-8'); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        MAC
        <input name="mac" type="text" placeholder="ex: aa:bb:cc:dd:ee:ff" />
      </label>
      <label>
        IP
        <input name="ipaddr" type="text" placeholder="ex: 192.168.1.50" />
      </label>
      <label>
        Descricao
        <input name="descr" type="text" placeholder="ex: Impressora sala" />
      </label>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
      <div class="form-status" id="dhcp-static-status">Aguardando...</div>
    </form>
  </div>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Servicos', $content);
