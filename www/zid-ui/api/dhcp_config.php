<?php
require_once __DIR__ . '/../app/bootstrap.php';

zid_ui_require_login();
zid_ui_require_admin();

$iface = isset($_REQUEST['interface']) ? $_REQUEST['interface'] : 'lan';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cfg = zid_ui_get_dhcp_config($iface);
    zid_ui_json_ok($cfg);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zid_ui_json_error('Metodo invalido', null, 405);
}

$token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
if (!zid_ui_csrf_validate($token)) {
    zid_ui_json_error('CSRF invalido', null, 403);
}

$range_from = isset($_POST['range_from']) ? trim($_POST['range_from']) : '';
$range_to = isset($_POST['range_to']) ? trim($_POST['range_to']) : '';
$enable = isset($_POST['enable']) && $_POST['enable'] === '1';

if ($range_from === '' || $range_to === '') {
    zid_ui_json_error('Range invalido', null, 400);
}

$ok = zid_ui_set_dhcp_config($iface, $range_from, $range_to, $enable);
if (!$ok) {
    zid_ui_json_error('Falha ao salvar configuracao', null, 500);
}

zid_ui_audit_log('dhcp_config', '/api/dhcp_config.php', array(
    'interface' => $iface,
    'range_from' => $range_from,
    'range_to' => $range_to,
    'enable' => $enable,
));

zid_ui_json_ok(array('saved' => true));
