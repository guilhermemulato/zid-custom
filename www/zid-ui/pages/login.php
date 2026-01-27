<?php
require_once __DIR__ . '/../app/bootstrap.php';

$redirect = isset($_REQUEST['r']) ? $_REQUEST['r'] : '/';
if (empty($redirect)) {
    $redirect = '/';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $GLOBALS['page'] = $redirect;
    $_POST['login'] = 'Login';
    session_auth();
}

if (zid_ui_is_logged_in()) {
    header('Location: /');
    exit;
}

$error = isset($_SESSION['Login_Error']) ? $_SESSION['Login_Error'] : '';
if ($error) {
    unset($_SESSION['Login_Error']);
}

ob_start();
?>
<section class="auth">
  <div class="auth-card">
    <div class="auth-brand">ZID UI</div>
    <div class="auth-title">Acesso seguro</div>
    <div class="auth-subtitle">Entre com suas credenciais</div>
    <?php if ($error): ?>
      <div class="auth-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" action="/login">
      <input type="hidden" name="r" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>" />
      <div class="auth-field">
        <label for="usernamefld">Usuario</label>
        <input id="usernamefld" name="usernamefld" type="text" autocomplete="username" required />
      </div>
      <div class="auth-field">
        <label for="passwordfld">Senha</label>
        <input id="passwordfld" name="passwordfld" type="password" autocomplete="current-password" required />
      </div>
      <button class="btn btn-primary auth-submit" type="submit">Entrar</button>
    </form>
  </div>
</section>
<?php
$content = ob_get_clean();

zid_ui_render_layout('ZID UI - Login', $content, array('show_topbar' => false, 'show_sidebar' => false));
