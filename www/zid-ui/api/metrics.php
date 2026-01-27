<?php
require_once __DIR__ . '/../app/bootstrap.php';
zid_ui_require_login();

$metrics = zid_ui_get_metrics();

zid_ui_json_ok($metrics);
