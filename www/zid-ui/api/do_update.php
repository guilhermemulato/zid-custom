<?php
require_once __DIR__ . '/../app/bootstrap.php';

zid_ui_require_login();
zid_ui_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zid_ui_json_error('Metodo invalido', null, 405);
}

$token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
if (!zid_ui_csrf_validate($token)) {
    zid_ui_json_error('CSRF invalido', null, 403);
}

$cmd = 'sudo /usr/local/etc/zid-ui/update.sh';
$output = array();
$rc = 0;
@exec($cmd . ' 2>&1', $output, $rc);

zid_ui_audit_log('update', '/api/do_update.php', array('rc' => $rc, 'output' => array_slice($output, -50)));

if ($rc !== 0) {
    zid_ui_json_error('Falha no update', array('rc' => $rc, 'output' => $output), 500);
}

zid_ui_json_ok(array('output' => $output));
