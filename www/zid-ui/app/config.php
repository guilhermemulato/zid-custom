<?php
function zid_ui_get_config($key, $default = '') {
    $conf = '/usr/local/etc/zid-ui/zid-ui.conf';
    if (!file_exists($conf)) {
        return $default;
    }
    $value = $default;
    $lines = @file($conf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return $default;
    }
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        if ($k === $key) {
            $value = trim($v);
            break;
        }
    }
    return $value;
}
