<?php
function zid_ui_is_logged_in() {
    if (function_exists('logged_in')) {
        return logged_in();
    }
    if (function_exists('is_authenticated')) {
        return is_authenticated();
    }
    return true;
}

function zid_ui_is_admin() {
    if (function_exists('is_admin')) {
        $user = isset($_SESSION['Username']) ? $_SESSION['Username'] : null;
        return is_admin($user);
    }
    if (function_exists('user_has_priv')) {
        return user_has_priv('page-all');
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

function zid_ui_require_login() {
    if (!zid_ui_is_logged_in()) {
        header('Location: ' . zid_ui_login_url());
        exit;
    }
}

function zid_ui_require_admin() {
    if (!zid_ui_is_admin()) {
        http_response_code(403);
        echo 'Admin only.';
        exit;
    }
}
