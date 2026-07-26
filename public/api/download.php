<?php
require_once dirname(__DIR__) . '/includes/session.php';

$token = session_token($_GET['token'] ?? '');
$name = basename($_GET['name'] ?? '');
if (!$token || !preg_match('/^[a-f0-9-]{36}\.enc$/', $name)) { http_response_code(400); exit; }
$session = open_session($token);
if (!$session) { http_response_code(410); exit; }
$path = $session['dir'] . '/' . $name;
if (!is_file($path)) { http_response_code(404); exit; }
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="encrypted-file.enc"');
header('Cache-Control: no-store');
readfile($path);
