<?php
require_once __DIR__ . '/../app/bootstrap.php';

zid_ui_require_login();

$dhcp = zid_ui_get_dhcp_status();

zid_ui_json_ok(array(
    'timestamp' => time(),
    'dhcp' => $dhcp,
));
