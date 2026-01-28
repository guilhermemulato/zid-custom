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
$dns1 = isset($_POST['dns1']) ? trim($_POST['dns1']) : '';
$dns2 = isset($_POST['dns2']) ? trim($_POST['dns2']) : '';
$default_leasetime = isset($_POST['default_leasetime']) ? trim($_POST['default_leasetime']) : '';
$max_leasetime = isset($_POST['max_leasetime']) ? trim($_POST['max_leasetime']) : '';
$gateway = isset($_POST['gateway']) ? trim($_POST['gateway']) : '';

if ($range_from === '' || $range_to === '') {
    zid_ui_json_error('Range invalido', null, 400);
}

$dnsservers = array();
if ($dns1 !== '') {
    if (!filter_var($dns1, FILTER_VALIDATE_IP)) {
        zid_ui_json_error('DNS 1 invalido', null, 400);
    }
    $dnsservers[] = $dns1;
}
if ($dns2 !== '') {
    if (!filter_var($dns2, FILTER_VALIDATE_IP)) {
        zid_ui_json_error('DNS 2 invalido', null, 400);
    }
    $dnsservers[] = $dns2;
}
if ($default_leasetime !== '' && !ctype_digit($default_leasetime)) {
    zid_ui_json_error('Lease padrao invalida', null, 400);
}
if ($max_leasetime !== '' && !ctype_digit($max_leasetime)) {
    zid_ui_json_error('Lease maxima invalida', null, 400);
}
if ($gateway !== '' && !filter_var($gateway, FILTER_VALIDATE_IP)) {
    zid_ui_json_error('Gateway invalido', null, 400);
}

$ok = zid_ui_set_dhcp_config($iface, $range_from, $range_to, $enable, $dnsservers, $default_leasetime, $max_leasetime, $gateway);
if (!$ok) {
    zid_ui_json_error('Falha ao salvar configuracao', null, 500);
}

zid_ui_audit_log('dhcp_config', '/api/dhcp_config.php', array(
    'interface' => $iface,
    'range_from' => $range_from,
    'range_to' => $range_to,
    'enable' => $enable,
    'dnsservers' => $dnsservers,
    'default_leasetime' => $default_leasetime,
    'max_leasetime' => $max_leasetime,
    'gateway' => $gateway,
));

zid_ui_json_ok(array('saved' => true));
