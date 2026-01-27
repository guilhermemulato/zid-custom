<?php
ob_start();
?>
<section class="hero">
  <h1>Em desenvolvimento</h1>
  <p>Conteudo sera implementado nas proximas fases.</p>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Em desenvolvimento', $content);
