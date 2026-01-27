<?php
function zid_ui_json_ok($data = array(), $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(array('ok' => true, 'data' => $data));
    exit;
}

function zid_ui_json_error($message, $details = null, $status = 400) {
    http_response_code($status);
    header('Content-Type: application/json');
    $payload = array('ok' => false, 'error' => $message);
    if ($details !== null) {
        $payload['details'] = $details;
    }
    echo json_encode($payload);
    exit;
}
