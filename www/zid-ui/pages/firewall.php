<?php
$interfaces = zid_ui_firewall_interfaces();
ob_start();
?>
<section class="hero">
  <div class="hero-header">
    <div>
      <h1>Firewall Rules</h1>
      <p>Gerencie regras por interface.</p>
    </div>
    <div class="hero-actions">
      <button class="btn btn-primary" id="fw-add-toggle">+ Add Rule</button>
    </div>
  </div>
</section>

<section class="panel" id="fw-panel">
  <div class="fw-toolbar">
    <div class="fw-tabs" id="fw-tabs">
      <?php $first = true; ?>
      <?php foreach ($interfaces as $if => $descr): ?>
        <button class="fw-tab <?php echo $first ? 'active' : ''; ?>" data-iface="<?php echo htmlspecialchars($if, ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars(strtoupper($if), ENT_QUOTES, 'UTF-8'); ?>
        </button>
        <?php $first = false; ?>
      <?php endforeach; ?>
    </div>
    <div class="fw-filter">
      <input type="text" id="fw-filter" placeholder="Filtrar regras..." />
      <button class="btn btn-outline" id="fw-filter-btn">Filtro</button>
    </div>
  </div>

  <div class="fw-header">
    <div>On</div>
    <div>Protocol</div>
    <div>Source</div>
    <div>Destination</div>
    <div>Port</div>
    <div>Description</div>
    <div></div>
  </div>

  <div class="fw-list" id="fw-list">
    <div class="fw-empty">Carregando regras...</div>
  </div>

  <div class="fw-dropzone">Drag and drop rules to reorder priority</div>
</section>

<section class="panel is-hidden" id="fw-add-panel">
  <div class="panel-header">
    <h2>Nova regra</h2>
    <span class="panel-meta">Configuracao essencial</span>
  </div>
  <form class="form-grid" id="fw-add-form">
    <label>
      Interface
      <select name="interface">
        <?php foreach ($interfaces as $if => $descr): ?>
          <option value="<?php echo htmlspecialchars($if, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($descr, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Acao
      <select name="type">
        <option value="pass">Permitir</option>
        <option value="block">Bloquear</option>
        <option value="reject">Rejeitar</option>
      </select>
    </label>
    <label>
      IP
      <select name="ipprotocol">
        <option value="inet">IPv4</option>
        <option value="inet6">IPv6</option>
        <option value="inet46">IPv4/IPv6</option>
      </select>
    </label>
    <label>
      Protocolo
      <select name="protocol">
        <option value="any">Qualquer</option>
        <option value="tcp">TCP</option>
        <option value="udp">UDP</option>
        <option value="tcp/udp">TCP/UDP</option>
        <option value="icmp">ICMP</option>
      </select>
    </label>
    <label>
      Origem
      <select name="src_type">
        <option value="any">Qualquer</option>
        <option value="interface_net">Rede da interface</option>
        <option value="interface_addr">Endereco da interface</option>
        <option value="address">Endereco especifico</option>
      </select>
    </label>
    <label>
      Origem (valor)
      <input name="src_value" type="text" placeholder="ex: 192.168.1.10" />
    </label>
    <label>
      Destino
      <select name="dst_type">
        <option value="any">Qualquer</option>
        <option value="interface_net">Rede da interface</option>
        <option value="interface_addr">Endereco da interface</option>
        <option value="address">Endereco especifico</option>
      </select>
    </label>
    <label>
      Destino (valor)
      <input name="dst_value" type="text" placeholder="ex: 10.0.0.1" />
    </label>
    <label>
      Porta destino
      <input name="dst_port" type="text" placeholder="ex: 443" />
    </label>
    <label>
      Descricao
      <input name="descr" type="text" placeholder="ex: Liberar HTTPS" />
    </label>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
    <div class="form-status" id="fw-add-status">Aguardando...</div>
  </form>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Firewall', $content);
