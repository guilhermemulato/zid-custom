<?php
require_once __DIR__ . '/../app/bootstrap.php';
zid_ui_require_login();

$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : null;

zid_ui_json_ok(array(
    'timestamp' => time(),
    'user' => $user,
    'status' => 'ok',
));
