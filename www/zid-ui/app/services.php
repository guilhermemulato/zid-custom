<?php
function zid_ui_get_services() {
    if (function_exists('get_services')) {
        return get_services();
    }
    if (function_exists('get_pfSense_services')) {
        return get_pfSense_services();
    }
    return array();
}

function zid_ui_get_dhcp_status() {
    $status = array(
        'enabled' => false,
        'running' => false,
        'interfaces' => array(),
        'backend' => '',
    );

    if (function_exists('dhcp_is_backend')) {
        $status['backend'] = dhcp_is_backend('kea') ? 'kea' : 'isc';
    }

    if (function_exists('is_service_enabled')) {
        $status['enabled'] = is_service_enabled('dhcpd') || is_service_enabled('kea-dhcp4');
    } else if (function_exists('config_get_path')) {
        $dhcpd = config_get_path('dhcpd', array());
        if (is_array($dhcpd)) {
            foreach ($dhcpd as $if => $cfg) {
                if (is_array($cfg) && isset($cfg['enable'])) {
                    $status['enabled'] = true;
                    $status['interfaces'][] = $if;
                }
            }
        }
    }

    $output = array();
    $rc = 1;
    @exec('/usr/bin/pgrep -a dhcpd 2>/dev/null', $output, $rc);
    if ($rc === 0 && !empty($output)) {
        $status['running'] = true;
    }

    return $status;
}

function zid_ui_get_dhcp_config($iface) {
    $cfg = array(
        'interface' => $iface,
        'enable' => false,
        'range_from' => '',
        'range_to' => '',
    );

    if (function_exists('config_get_path')) {
        $dhcp = config_get_path("dhcpd/{$iface}", array());
        if (!empty($dhcp)) {
            $cfg['enable'] = isset($dhcp['enable']);
            if (isset($dhcp['range']['from'])) {
                $cfg['range_from'] = $dhcp['range']['from'];
            }
            if (isset($dhcp['range']['to'])) {
                $cfg['range_to'] = $dhcp['range']['to'];
            }
        }
    }

    return $cfg;
}

function zid_ui_set_dhcp_config($iface, $range_from, $range_to, $enable) {
    if (!function_exists('config_set_path')) {
        return false;
    }

    $path = "dhcpd/{$iface}";
    $cfg = config_get_path($path, array());
    if (!is_array($cfg)) {
        $cfg = array();
    }

    $cfg['range'] = array('from' => $range_from, 'to' => $range_to);
    if ($enable) {
        $cfg['enable'] = true;
    } else {
        unset($cfg['enable']);
    }

    config_set_path($path, $cfg);
    if (function_exists('write_config')) {
        write_config('ZID UI: update DHCP range');
    }

    if (function_exists('services_dhcpd_configure')) {
        services_dhcpd_configure();
    }

    return true;
}
