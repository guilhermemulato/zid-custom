<?php
require_once __DIR__ . '/../app/bootstrap.php';
zid_ui_require_login();

$lines = isset($_GET['lines']) ? intval($_GET['lines']) : 50;
if ($lines < 1 || $lines > 500) {
    $lines = 50;
}

$log_file = '/var/log/system.log';
if (!file_exists($log_file)) {
    zid_ui_json_ok(array('lines' => array()));
}

$output = array();
$rc = 0;
$cmd = '/usr/bin/tail -n ' . $lines . ' ' . escapeshellarg($log_file);
@exec($cmd, $output, $rc);

if ($rc !== 0) {
    zid_ui_json_ok(array('lines' => array()));
}

zid_ui_json_ok(array('lines' => $output));
