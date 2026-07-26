<?php
/* API texte partagé : GET → {text}, POST → enregistre */
require_once __DIR__ . '/../includes/storage.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

$input  = ($method === 'GET') ? $_GET : json_decode(file_get_contents('php://input'), true);
$token = normalizeToken($input['token'] ?? null);
if ($token === null) { http_response_code(400); exit; }

$dir = sessionDirectory($token);
$file   = $dir.'/bloc-notes.txt';

if ($method === 'GET') {
    echo json_encode(['text'=> file_exists($file) ? file_get_contents($file) : '']);
    exit;
}

if ($method === 'POST') {
    $text = $input['text'] ?? '';
    // On limite à 20 000 caractères pour éviter les abus
    if (mb_strlen($text) > 20000) { http_response_code(413); exit; }
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        http_response_code(500);
        exit;
    }
    if (file_put_contents($file, $text, LOCK_EX) === false) {
        http_response_code(500);
        exit;
    }
    echo json_encode(['ok'=>true]);
    exit;
}

http_response_code(405);
