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

$service = isset($_POST['service']) ? $_POST['service'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

$allowed_services = array('dhcpd');
$allowed_actions = array('start', 'stop', 'restart', 'status', 'onestatus');

if (!in_array($service, $allowed_services, true)) {
    zid_ui_json_error('Servico nao permitido', null, 400);
}
if (!in_array($action, $allowed_actions, true)) {
    zid_ui_json_error('Acao nao permitida', null, 400);
}

$cmd = 'sudo /usr/local/etc/zid-ui/service.sh ' . escapeshellarg($service) . ' ' . escapeshellarg($action);
$output = array();
$rc = 0;
@exec($cmd . ' 2>&1', $output, $rc);

zid_ui_audit_log('service_action', '/api/service_action.php', array(
    'service' => $service,
    'action' => $action,
    'rc' => $rc,
    'output' => array_slice($output, -50),
));

if ($rc !== 0) {
    zid_ui_json_error('Falha ao executar acao', array('rc' => $rc, 'output' => $output), 500);
}

zid_ui_json_ok(array('output' => $output));
