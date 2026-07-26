<?php
require_once dirname(__DIR__) . '/includes/session.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = $method === 'GET' ? $_GET : json_decode((string) file_get_contents('php://input'), true);
$token = session_token($input['token'] ?? '');
if (!$token) json_error(400, 'Token invalide');
$session = open_session($token);
if (!$session) json_error(410, 'Session expirée');
$file = $session['dir'] . '/notes.enc';

if ($method === 'GET') {
    echo json_encode(['ciphertext' => is_file($file) ? file_get_contents($file) : '']);
    exit;
}
if ($method !== 'POST') json_error(405, 'Méthode non autorisée');
$ciphertext = $input['ciphertext'] ?? '';
if (!is_string($ciphertext) || strlen($ciphertext) > 40000 || !preg_match('/^[A-Za-z0-9_-]*$/', $ciphertext)) {
    json_error(413, 'Contenu chiffré invalide ou trop volumineux');
}
if (file_put_contents($file, $ciphertext, LOCK_EX) === false) json_error(500, 'Écriture impossible');
echo json_encode(['ok' => true]);
