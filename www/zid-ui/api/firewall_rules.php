<?php
require_once __DIR__ . '/../app/bootstrap.php';

zid_ui_require_login();

$iface = isset($_REQUEST['interface']) ? $_REQUEST['interface'] : 'lan';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = zid_ui_firewall_get_rules($iface);
    zid_ui_json_ok(array('items' => $items));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zid_ui_json_error('Metodo invalido', null, 405);
}

zid_ui_require_admin();

$token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
if (!zid_ui_csrf_validate($token)) {
    zid_ui_json_error('CSRF invalido', null, 403);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'toggle') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $enabled = isset($_POST['enabled']) && $_POST['enabled'] === '1';
    if ($id === '') {
        zid_ui_json_error('Regra invalida', null, 400);
    }
    if (!zid_ui_firewall_toggle_rule($id, $enabled)) {
        zid_ui_json_error('Regra nao encontrada', null, 404);
    }
    zid_ui_json_ok(array('saved' => true));
}

if ($action === 'copy') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    if ($id === '') {
        zid_ui_json_error('Regra invalida', null, 400);
    }
    if (!zid_ui_firewall_copy_rule($id)) {
        zid_ui_json_error('Regra nao encontrada', null, 404);
    }
    zid_ui_json_ok(array('saved' => true));
}

if ($action === 'reorder') {
    $order = array();
    if (isset($_POST['order'])) {
        $order = json_decode($_POST['order'], true);
    } elseif (isset($_POST['order_ids'])) {
        $order = $_POST['order_ids'];
    }
    if (!is_array($order) || empty($order)) {
        zid_ui_json_error('Ordem invalida', null, 400);
    }
    zid_ui_firewall_reorder($iface, $order);
    zid_ui_json_ok(array('saved' => true));
}

if ($action === 'add') {
    $interfaces = zid_ui_firewall_interfaces();
    if (!isset($interfaces[$iface])) {
        zid_ui_json_error('Interface invalida', null, 400);
    }

    $type = isset($_POST['type']) ? $_POST['type'] : 'pass';
    $ipprotocol = isset($_POST['ipprotocol']) ? $_POST['ipprotocol'] : 'inet';
    $protocol = isset($_POST['protocol']) ? $_POST['protocol'] : 'any';
    $descr = isset($_POST['descr']) ? trim($_POST['descr']) : '';
    $dst_port = isset($_POST['dst_port']) ? trim($_POST['dst_port']) : '';

    $valid_type = array('pass', 'block', 'reject');
    $valid_ipproto = array('inet', 'inet6', 'inet46');
    $valid_proto = array('any', 'tcp', 'udp', 'tcp/udp', 'icmp');
    if (!in_array($type, $valid_type, true)) {
        zid_ui_json_error('Tipo invalido', null, 400);
    }
    if (!in_array($ipprotocol, $valid_ipproto, true)) {
        zid_ui_json_error('IP protocolo invalido', null, 400);
    }
    if (!in_array($protocol, $valid_proto, true)) {
        zid_ui_json_error('Protocolo invalido', null, 400);
    }

    $src_type = isset($_POST['src_type']) ? $_POST['src_type'] : 'any';
    $src_value = isset($_POST['src_value']) ? trim($_POST['src_value']) : '';
    $dst_type = isset($_POST['dst_type']) ? $_POST['dst_type'] : 'any';
    $dst_value = isset($_POST['dst_value']) ? trim($_POST['dst_value']) : '';

    $build_endpoint = function ($type, $value, $iface, $suffix) {
        if ($type === 'any') {
            return array('any' => '');
        }
        if ($type === 'interface_net') {
            return array('network' => $iface);
        }
        if ($type === 'interface_addr') {
            return array('address' => $iface . 'ip');
        }
        if ($type === 'address') {
            return array('address' => $value);
        }
        return array('any' => '');
    };

    $source = $build_endpoint($src_type, $src_value, $iface, 'Net');
    $destination = $build_endpoint($dst_type, $dst_value, $iface, 'Address');
    if ($dst_port !== '') {
        $destination['port'] = $dst_port;
    }

    $payload = array(
        'interface' => $iface,
        'action' => $type,
        'ipprotocol' => $ipprotocol,
        'protocol' => $protocol,
        'source' => $source,
        'destination' => $destination,
        'descr' => $descr,
    );

    zid_ui_firewall_add_rule($payload);
    zid_ui_json_ok(array('saved' => true));
}

zid_ui_json_error('Acao invalida', null, 400);
