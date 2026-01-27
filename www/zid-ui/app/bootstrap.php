<?php
// Bootstrap minimo para integrar com o ambiente do pfSense quando disponivel.

if (!defined('ZID_UI_ROOT')) {
    define('ZID_UI_ROOT', dirname(__DIR__));
}
if (!defined('ZID_UI_APP')) {
    define('ZID_UI_APP', ZID_UI_ROOT . '/app');
}
if (!defined('ZID_UI_PAGES')) {
    define('ZID_UI_PAGES', ZID_UI_ROOT . '/pages');
}
if (!defined('ZID_UI_API')) {
    define('ZID_UI_API', ZID_UI_ROOT . '/api');
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$inc_path = ini_get('include_path');
if (strpos($inc_path, '/etc/inc') === false) {
    ini_set('include_path', $inc_path . PATH_SEPARATOR . '/etc/inc');
}

$pf_includes = array(
    '/etc/inc/config.inc',
    '/etc/inc/config.lib.inc',
    '/etc/inc/util.inc',
    '/etc/inc/functions.inc',
    '/etc/inc/auth.inc',
    '/etc/inc/priv.inc',
);

foreach ($pf_includes as $pf_file) {
    if (file_exists($pf_file)) {
        require_once $pf_file;
    }
}

require_once ZID_UI_APP . '/auth.php';
require_once ZID_UI_APP . '/csrf.php';
require_once ZID_UI_APP . '/config.php';
require_once ZID_UI_APP . '/login.php';
require_once ZID_UI_APP . '/response.php';
require_once ZID_UI_APP . '/layout.php';
require_once ZID_UI_APP . '/metrics.php';
require_once ZID_UI_APP . '/services.php';
require_once ZID_UI_APP . '/geo.php';
require_once ZID_UI_APP . '/audit.php';
