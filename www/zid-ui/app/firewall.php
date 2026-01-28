<?php
if (file_exists('/etc/inc/filter.inc')) {
    require_once '/etc/inc/filter.inc';
}

function zid_ui_firewall_interfaces() {
    if (function_exists('get_configured_interface_with_descr')) {
        return get_configured_interface_with_descr();
    }
    if (function_exists('get_configured_interface_list')) {
        $interfaces = array();
        foreach (get_configured_interface_list() as $if) {
            $interfaces[$if] = strtoupper($if);
        }
        return $interfaces;
    }
    return array('lan' => 'LAN', 'wan' => 'WAN');
}

function zid_ui_firewall_rule_id($rule, $index) {
    if (isset($rule['tracker']) && $rule['tracker'] !== '') {
        return (string)$rule['tracker'];
    }
    return 'idx-' . (string)$index;
}

function zid_ui_firewall_rule_matches_iface($rule, $iface) {
    if (!isset($rule['interface'])) {
        return false;
    }
    if (is_array($rule['interface'])) {
        return in_array($iface, $rule['interface'], true);
    }
    return $rule['interface'] === $iface;
}

function zid_ui_firewall_iface_label($iface, $iface_map, $suffix) {
    $label = isset($iface_map[$iface]) ? $iface_map[$iface] : strtoupper($iface);
    return trim($label . ' ' . $suffix);
}

function zid_ui_firewall_format_endpoint($endpoint, $iface_map, $suffix_net, $suffix_addr) {
    $label = '*';
    if (isset($endpoint['any'])) {
        $label = '*';
    } elseif (isset($endpoint['network'])) {
        $net = $endpoint['network'];
        if (isset($iface_map[$net])) {
            $label = zid_ui_firewall_iface_label($net, $iface_map, $suffix_net);
        } else {
            $label = $net;
        }
    } elseif (isset($endpoint['address'])) {
        $addr = $endpoint['address'];
        if (isset($iface_map[$addr])) {
            $label = zid_ui_firewall_iface_label($addr, $iface_map, $suffix_addr);
        } else {
            foreach ($iface_map as $if => $descr) {
                if ($addr === ($if . 'ip')) {
                    $label = zid_ui_firewall_iface_label($if, $iface_map, $suffix_addr);
                    break;
                }
            }
            if ($label === '*') {
                $label = $addr;
            }
        }
    }

    if (isset($endpoint['not'])) {
        $label = '!' . $label;
    }

    return $label;
}

function zid_ui_firewall_protocol_label($rule) {
    $ipproto = isset($rule['ipprotocol']) ? $rule['ipprotocol'] : '';
    $proto = isset($rule['protocol']) ? $rule['protocol'] : '';

    $ip_label = '*';
    if ($ipproto === 'inet' || $ipproto === 'inet4') {
        $ip_label = 'IPv4';
    } elseif ($ipproto === 'inet6') {
        $ip_label = 'IPv6';
    } elseif ($ipproto === 'inet46') {
        $ip_label = 'IPv4/IPv6';
    }

    $proto_label = '*';
    if ($proto !== '' && $proto !== 'any') {
        $proto_label = strtoupper($proto);
    }

    return trim($ip_label . ' ' . $proto_label);
}

function zid_ui_firewall_get_rules($iface) {
    $rules = config_get_path('filter/rule', array());
    $iface_map = zid_ui_firewall_interfaces();
    $items = array();

    foreach ($rules as $idx => $rule) {
        if (!zid_ui_firewall_rule_matches_iface($rule, $iface)) {
            continue;
        }
        $source = isset($rule['source']) && is_array($rule['source']) ? $rule['source'] : array('any' => '');
        $destination = isset($rule['destination']) && is_array($rule['destination']) ? $rule['destination'] : array('any' => '');
        $items[] = array(
            'id' => zid_ui_firewall_rule_id($rule, $idx),
            'enabled' => !isset($rule['disabled']),
            'action' => isset($rule['type']) ? $rule['type'] : 'pass',
            'protocol' => zid_ui_firewall_protocol_label($rule),
            'source' => zid_ui_firewall_format_endpoint($source, $iface_map, 'Net', 'Address'),
            'destination' => zid_ui_firewall_format_endpoint($destination, $iface_map, 'Net', 'Address'),
            'port' => isset($destination['port']) ? (string)$destination['port'] : '*',
            'descr' => isset($rule['descr']) ? $rule['descr'] : '',
        );
    }

    return $items;
}

function zid_ui_firewall_find_rule_index($rules, $id) {
    if (strpos($id, 'idx-') === 0) {
        $idx = (int)substr($id, 4);
        if (isset($rules[$idx])) {
            return $idx;
        }
    }

    foreach ($rules as $idx => $rule) {
        if (isset($rule['tracker']) && (string)$rule['tracker'] === (string)$id) {
            return $idx;
        }
    }
    return null;
}

function zid_ui_firewall_apply_rules($rules, $log_label) {
    config_set_path('filter/rule', $rules);
    if (function_exists('write_config')) {
        write_config($log_label);
    }
    if (function_exists('filter_configure')) {
        filter_configure();
    }
}

function zid_ui_firewall_toggle_rule($id, $enabled) {
    $rules = config_get_path('filter/rule', array());
    $idx = zid_ui_firewall_find_rule_index($rules, $id);
    if ($idx === null) {
        return false;
    }
    if ($enabled) {
        unset($rules[$idx]['disabled']);
    } else {
        $rules[$idx]['disabled'] = true;
    }
    zid_ui_firewall_apply_rules($rules, 'ZID UI: toggle firewall rule');
    return true;
}

function zid_ui_firewall_copy_rule($id) {
    $rules = config_get_path('filter/rule', array());
    $idx = zid_ui_firewall_find_rule_index($rules, $id);
    if ($idx === null) {
        return false;
    }
    $rule = $rules[$idx];
    $rule['tracker'] = function_exists('filter_rule_tracker') ? filter_rule_tracker() : (string)time();
    $rule['created'] = array('time' => time(), 'username' => 'ZID UI');
    if (isset($rule['descr']) && $rule['descr'] !== '') {
        $rule['descr'] = $rule['descr'] . ' (copia)';
    }
    array_splice($rules, $idx + 1, 0, array($rule));
    zid_ui_firewall_apply_rules($rules, 'ZID UI: copy firewall rule');
    return true;
}

function zid_ui_firewall_reorder($iface, $order_ids) {
    $rules = config_get_path('filter/rule', array());
    $iface_rules = array();
    foreach ($rules as $idx => $rule) {
        if (zid_ui_firewall_rule_matches_iface($rule, $iface)) {
            $iface_rules[] = $rule;
        }
    }

    $map = array();
    foreach ($iface_rules as $idx => $rule) {
        $map[zid_ui_firewall_rule_id($rule, $idx)] = $rule;
    }

    $ordered = array();
    foreach ($order_ids as $id) {
        if (isset($map[$id])) {
            $ordered[] = $map[$id];
            unset($map[$id]);
        }
    }
    foreach ($map as $rule) {
        $ordered[] = $rule;
    }

    $ordered_idx = 0;
    foreach ($rules as $idx => $rule) {
        if (zid_ui_firewall_rule_matches_iface($rule, $iface)) {
            $rules[$idx] = $ordered[$ordered_idx];
            $ordered_idx++;
        }
    }

    zid_ui_firewall_apply_rules($rules, 'ZID UI: reorder firewall rules');
    return true;
}

function zid_ui_firewall_add_rule($payload) {
    $iface = $payload['interface'];
    $action = $payload['action'];
    $ipproto = $payload['ipprotocol'];
    $protocol = $payload['protocol'];
    $source = $payload['source'];
    $destination = $payload['destination'];
    $descr = $payload['descr'];

    $rule = array(
        'type' => $action,
        'interface' => $iface,
        'ipprotocol' => $ipproto,
        'source' => $source,
        'destination' => $destination,
        'descr' => $descr,
        'tracker' => function_exists('filter_rule_tracker') ? filter_rule_tracker() : (string)time(),
        'created' => array('time' => time(), 'username' => 'ZID UI'),
    );

    if ($protocol !== 'any') {
        $rule['protocol'] = $protocol;
    }

    $rules = config_get_path('filter/rule', array());
    $insert_idx = count($rules);
    foreach ($rules as $idx => $r) {
        if (zid_ui_firewall_rule_matches_iface($r, $iface)) {
            $insert_idx = $idx + 1;
        }
    }
    array_splice($rules, $insert_idx, 0, array($rule));
    zid_ui_firewall_apply_rules($rules, 'ZID UI: add firewall rule');
    return true;
}
