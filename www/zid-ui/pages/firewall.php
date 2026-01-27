<?php
ob_start();
?>
<section class="hero">
  <h1>Firewall</h1>
  <p>Secao em desenvolvimento.</p>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Firewall', $content);
