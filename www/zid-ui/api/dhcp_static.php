<?php
require_once __DIR__ . '/../app/bootstrap.php';

zid_ui_require_login();
zid_ui_require_admin();

$iface = isset($_REQUEST['interface']) ? $_REQUEST['interface'] : 'lan';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = zid_ui_get_dhcp_static_maps($iface);
    zid_ui_json_ok(array('items' => $items));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zid_ui_json_error('Metodo invalido', null, 405);
}

$token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
if (!zid_ui_csrf_validate($token)) {
    zid_ui_json_error('CSRF invalido', null, 403);
}

$mac = isset($_POST['mac']) ? trim($_POST['mac']) : '';
$ipaddr = isset($_POST['ipaddr']) ? trim($_POST['ipaddr']) : '';
$descr = isset($_POST['descr']) ? trim($_POST['descr']) : '';

if ($mac === '' || $ipaddr === '') {
    zid_ui_json_error('MAC e IP sao obrigatorios', null, 400);
}

if (!preg_match('/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/', $mac)) {
    zid_ui_json_error('MAC invalido', null, 400);
}

if (!filter_var($ipaddr, FILTER_VALIDATE_IP)) {
    zid_ui_json_error('IP invalido', null, 400);
}

$result = zid_ui_add_dhcp_static_map($iface, $mac, $ipaddr, $descr);
if (!$result['ok']) {
    zid_ui_json_error($result['error'], null, 400);
}

zid_ui_audit_log('dhcp_static', '/api/dhcp_static.php', array(
    'interface' => $iface,
    'mac' => $mac,
    'ipaddr' => $ipaddr,
    'descr' => $descr,
    'updated' => $result['updated'],
));

zid_ui_json_ok(array('saved' => true, 'updated' => $result['updated']));
