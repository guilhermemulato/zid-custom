<?php
require_once __DIR__ . '/../app/bootstrap.php';
zid_ui_require_login();

$since = isset($_GET['since']) ? intval($_GET['since']) : 0;
$events = zid_ui_get_geo_events($since);

zid_ui_json_ok($events);
