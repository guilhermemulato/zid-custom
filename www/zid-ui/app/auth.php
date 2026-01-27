<?php
function zid_ui_is_logged_in() {
    if (function_exists('session_auth')) {
        $GLOBALS['page'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        session_auth();
    }
    return !empty($_SESSION['Logged_In']) && $_SESSION['Logged_In'] === 'True';
}

function zid_ui_is_admin() {
    $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : null;
    if (!$username) {
        return false;
    }
    if (function_exists('getUserEntry')) {
        $entry = getUserEntry($username);
        if (!empty($entry) && isset($entry['item'])) {
            $user = $entry['item'];
            if (function_exists('get_user_privileges')) {
                $privs = get_user_privileges($user);
                if (is_array($privs) && in_array('page-all', $privs, true)) {
                    return true;
                }
            }
        }
    }
    return false;
}

function zid_ui_login_url() {
    $scheme = 'https';
    if (isset($GLOBALS['config']['system']['webgui']['protocol'])) {
        $scheme = $GLOBALS['config']['system']['webgui']['protocol'];
    }

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $host = preg_replace('/:\\d+$/', '', $host);

    $port = null;
    if (isset($GLOBALS['config']['system']['webgui']['port'])) {
        $port = $GLOBALS['config']['system']['webgui']['port'];
    } elseif (isset($GLOBALS['config']['system']['webgui_port'])) {
        $port = $GLOBALS['config']['system']['webgui_port'];
    }

    $url = $scheme . '://' . $host;
    if (!empty($port) && $port != '443' && $port != '80') {
        $url .= ':' . $port;
    }

    return $url . '/index.php';
}

function zid_ui_logout_url() {
    return '/logout';
}

function zid_ui_require_login() {
    if (zid_ui_is_logged_in()) {
        return;
    }

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, '/api/') === 0) {
        zid_ui_json_error('Nao autenticado', null, 401);
    }

    $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    zid_ui_render_login_page($redirect);
    exit;
}

function zid_ui_logout() {
    if (function_exists('phpsession_begin')) {
        phpsession_begin();
    } elseif (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    if (function_exists('phpsession_destroy')) {
        phpsession_destroy();
    } else {
        @session_destroy();
    }
}

function zid_ui_require_admin() {
    if (zid_ui_is_admin()) {
        return;
    }

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, '/api/') === 0) {
        zid_ui_json_error('Admin only', null, 403);
    }

    http_response_code(403);
    echo 'Admin only.';
    exit;
}
