<?php
function zid_ui_get_geo_events($since = 0) {
    $now = time();
    return array(
        'ts' => $now,
        'events' => array(
            array(
                'id' => 'br-sp',
                'lat' => -23.55,
                'lng' => -46.63,
                'label' => 'BR / Sao Paulo',
                'count' => 12,
                'country' => 'BR',
                'severity' => 'warning',
            ),
        ),
    );
}
