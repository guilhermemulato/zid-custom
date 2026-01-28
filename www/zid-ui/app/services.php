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
        'dnsservers' => array(),
        'default_leasetime' => '',
        'max_leasetime' => '',
        'gateway' => '',
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
            if (isset($dhcp['dnsserver'])) {
                $cfg['dnsservers'] = is_array($dhcp['dnsserver']) ? $dhcp['dnsserver'] : array($dhcp['dnsserver']);
            }
            if (isset($dhcp['defaultleasetime'])) {
                $cfg['default_leasetime'] = $dhcp['defaultleasetime'];
            }
            if (isset($dhcp['maxleasetime'])) {
                $cfg['max_leasetime'] = $dhcp['maxleasetime'];
            }
            if (isset($dhcp['gateway'])) {
                $cfg['gateway'] = $dhcp['gateway'];
            }
        }
    }

    return $cfg;
}

function zid_ui_set_dhcp_config($iface, $range_from, $range_to, $enable, $dnsservers, $default_leasetime, $max_leasetime, $gateway) {
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
    if (is_array($dnsservers) && !empty($dnsservers)) {
        $cfg['dnsserver'] = $dnsservers;
    } else {
        unset($cfg['dnsserver']);
    }
    if ($default_leasetime !== '') {
        $cfg['defaultleasetime'] = $default_leasetime;
    } else {
        unset($cfg['defaultleasetime']);
    }
    if ($max_leasetime !== '') {
        $cfg['maxleasetime'] = $max_leasetime;
    } else {
        unset($cfg['maxleasetime']);
    }
    if ($gateway !== '') {
        $cfg['gateway'] = $gateway;
    } else {
        unset($cfg['gateway']);
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

function zid_ui_get_dhcp_static_maps($iface) {
    $maps = array();
    if (!function_exists('config_get_path')) {
        return $maps;
    }

    $items = config_get_path("dhcpd/{$iface}/staticmap", array());
    if (!is_array($items)) {
        return $maps;
    }

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $maps[] = array(
            'mac' => isset($item['mac']) ? $item['mac'] : '',
            'ipaddr' => isset($item['ipaddr']) ? $item['ipaddr'] : '',
            'descr' => isset($item['descr']) ? $item['descr'] : '',
        );
    }

    return $maps;
}

function zid_ui_add_dhcp_static_map($iface, $mac, $ipaddr, $descr) {
    if (!function_exists('config_get_path') || !function_exists('config_set_path')) {
        return array('ok' => false, 'error' => 'Config indisponivel');
    }

    $path = "dhcpd/{$iface}/staticmap";
    $items = config_get_path($path, array());
    if (!is_array($items)) {
        $items = array();
    }

    $normalized_mac = strtolower($mac);
    $updated = false;

    foreach ($items as &$item) {
        if (!is_array($item)) {
            continue;
        }
        if (isset($item['ipaddr']) && $item['ipaddr'] === $ipaddr) {
            if (isset($item['mac']) && strtolower($item['mac']) !== $normalized_mac) {
                return array('ok' => false, 'error' => 'IP ja usado por outro MAC');
            }
        }
    }
    unset($item);

    foreach ($items as &$item) {
        if (!is_array($item)) {
            continue;
        }
        if (isset($item['mac']) && strtolower($item['mac']) === $normalized_mac) {
            $item['ipaddr'] = $ipaddr;
            $item['descr'] = $descr;
            $updated = true;
            break;
        }
    }
    unset($item);

    if (!$updated) {
        $items[] = array(
            'mac' => $mac,
            'ipaddr' => $ipaddr,
            'descr' => $descr,
        );
    }

    config_set_path($path, $items);
    if (function_exists('write_config')) {
        write_config('ZID UI: update DHCP static mapping');
    }
    if (function_exists('services_dhcpd_configure')) {
        services_dhcpd_configure();
    }

    return array('ok' => true, 'updated' => $updated);
}
