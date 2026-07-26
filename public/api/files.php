<?php
require_once dirname(__DIR__) . '/includes/session.php';
header('Content-Type: application/json');

$token = session_token($_GET['token'] ?? '');
if (!$token) json_error(400, 'Token invalide');
$session = open_session($token);
if (!$session) json_error(410, 'Session expirée');
header('X-Session-Expires-At: ' . ($session['expiresAt'] === null ? '' : $session['expiresAt'] * 1000));

$basePath = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
$base = $basePath . '/api/download.php?token=' . $token . '&name=';
$list = [];
foreach (glob($session['dir'] . '/*.enc') ?: [] as $file) {
    $id = basename($file);
    $list[] = ['id' => $id, 'size' => filesize($file), 'url' => $base . rawurlencode($id)];
}
echo json_encode($list);
