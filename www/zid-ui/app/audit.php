<?php
function zid_ui_db_path() {
    return '/var/db/zid-ui/zid-ui.db';
}

function zid_ui_db_init($db) {
    $db->exec('CREATE TABLE IF NOT EXISTS ui_preferences (
        user TEXT PRIMARY KEY,
        theme TEXT,
        sidebar_collapsed INTEGER,
        refresh_profile TEXT,
        updated_at INTEGER
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS audit_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ts INTEGER,
        user TEXT,
        action TEXT,
        route TEXT,
        payload_json TEXT
    )');
}

function zid_ui_db_get() {
    $path = zid_ui_db_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $db = new SQLite3($path);
    zid_ui_db_init($db);
    return $db;
}

function zid_ui_audit_log($action, $route, $payload = array()) {
    try {
        $db = zid_ui_db_get();
        $stmt = $db->prepare('INSERT INTO audit_log (ts, user, action, route, payload_json) VALUES (:ts, :user, :action, :route, :payload)');
        $stmt->bindValue(':ts', time(), SQLITE3_INTEGER);
        $stmt->bindValue(':user', isset($_SESSION['Username']) ? $_SESSION['Username'] : '', SQLITE3_TEXT);
        $stmt->bindValue(':action', $action, SQLITE3_TEXT);
        $stmt->bindValue(':route', $route, SQLITE3_TEXT);
        $stmt->bindValue(':payload', json_encode($payload), SQLITE3_TEXT);
        $stmt->execute();
        $db->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
