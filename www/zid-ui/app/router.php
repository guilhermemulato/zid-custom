<?php
function zid_ui_route_request() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/');
    if ($path === '') {
        $path = '/';
    }
    if ($path === '/index.php') {
        $path = '/';
    }

    if ($path === '/login') {
        require ZID_UI_PAGES . '/login.php';
        return;
    }
    if ($path === '/logout') {
        zid_ui_logout();
        header('Location: /login');
        exit;
    }

    if (strpos($path, '/api/') === 0) {
        zid_ui_require_login();
        $api_file = ZID_UI_API . $path;
        if (file_exists($api_file)) {
            require $api_file;
            return;
        }
        zid_ui_json_error('Endpoint nao encontrado', null, 404);
    }

    zid_ui_require_login();

    $routes = array(
        '/' => ZID_UI_PAGES . '/dashboard.php',
        '/dashboard' => ZID_UI_PAGES . '/dashboard.php',
        '/services' => ZID_UI_PAGES . '/services.php',
        '/firewall' => ZID_UI_PAGES . '/firewall.php',
        '/vpn' => ZID_UI_PAGES . '/vpn.php',
        '/firewall_rules' => ZID_UI_PAGES . '/firewall_rules.php',
        '/system_settings' => ZID_UI_PAGES . '/system_settings.php',
        '/zid_packages' => ZID_UI_PAGES . '/zid_packages.php',
    );

    if (isset($routes[$path]) && file_exists($routes[$path])) {
        require $routes[$path];
        return;
    }

    http_response_code(404);
    echo 'Pagina nao encontrada.';
}
