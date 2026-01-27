<?php
ob_start();
?>
<section class="hero">
  <h1>Dashboard ZID UI</h1>
  <p>Base inicial da interface. Widgets e mapa serao adicionados nas proximas fases.</p>
  <div class="cards">
    <div class="card">CPU</div>
    <div class="card">Memoria</div>
    <div class="card">Disco</div>
  </div>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Dashboard', $content);
