<?php
function zid_ui_csrf_token() {
    if (function_exists('csrf_get_token')) {
        return csrf_get_token();
    }
    if (function_exists('csrf_token')) {
        return csrf_token();
    }
    if (!isset($_SESSION['zid_ui_csrf'])) {
        $_SESSION['zid_ui_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['zid_ui_csrf'];
}

function zid_ui_csrf_validate($token) {
    if (function_exists('csrf_check_token')) {
        return csrf_check_token($token);
    }
    if (function_exists('csrf_verify')) {
        return csrf_verify($token);
    }
    $expected = isset($_SESSION['zid_ui_csrf']) ? $_SESSION['zid_ui_csrf'] : '';
    return is_string($token) && hash_equals($expected, $token);
}
