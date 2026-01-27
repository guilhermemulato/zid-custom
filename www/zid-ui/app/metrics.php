<?php
function zid_ui_get_metrics() {
    $metrics = array(
        'timestamp' => time(),
        'cpu' => null,
        'mem' => null,
        'disk' => null,
        'interfaces' => array(),
        'services' => array(),
    );

    if (function_exists('get_cpu_usage')) {
        $metrics['cpu'] = get_cpu_usage();
    } elseif (function_exists('sysctlbyname')) {
        $load = sysctlbyname('vm.loadavg');
        $metrics['cpu'] = is_string($load) ? $load : null;
    } elseif (function_exists('sys_getloadavg')) {
        $metrics['cpu'] = sys_getloadavg();
    }

    if (function_exists('get_mem_usage')) {
        $metrics['mem'] = get_mem_usage();
    } elseif (function_exists('sysctlbyname')) {
        $phys = sysctlbyname('hw.physmem');
        $metrics['mem'] = $phys ? array('physmem' => $phys) : null;
    }

    if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
        $root = '/';
        $metrics['disk'] = array(
            'free' => @disk_free_space($root),
            'total' => @disk_total_space($root),
        );
    }

    if (function_exists('get_interface_list')) {
        $metrics['interfaces'] = get_interface_list();
    } elseif (function_exists('get_configured_interface_with_descr')) {
        $metrics['interfaces'] = get_configured_interface_with_descr();
    }

    if (function_exists('get_pfSense_services')) {
        $metrics['services'] = get_pfSense_services();
    }

    return $metrics;
}
