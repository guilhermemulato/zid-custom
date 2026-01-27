<?php
function zid_ui_render_login_page($redirect = '/') {
    $_REQUEST['r'] = $redirect;
    require ZID_UI_PAGES . '/login.php';
}
