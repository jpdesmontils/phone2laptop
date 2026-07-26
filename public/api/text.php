<?php
/* API texte partagé : GET → {text}, POST → enregistre */
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

$input  = ($method === 'GET') ? $_GET : json_decode(file_get_contents('php://input'), true);
$token  = preg_replace('/[^a-f0-9]/','', $input['token'] ?? '');
if (!$token) { http_response_code(400); exit; }

$root   = dirname(__DIR__, 2);
$dir    = $root.'/uploads/'.$token;
if (!is_dir($dir)) @mkdir($dir, 0775, true);
$file   = $dir.'/bloc-notes.txt';

if ($method === 'GET') {
    echo json_encode(['text'=> file_exists($file) ? file_get_contents($file) : '']);
    exit;
}

if ($method === 'POST') {
    $text = $input['text'] ?? '';
    // On limite à 20 000 caractères pour éviter les abus
    if (mb_strlen($text) > 20000) { http_response_code(413); exit; }
    file_put_contents($file, $text, LOCK_EX);
    echo json_encode(['ok'=>true]);
    exit;
}

http_response_code(405);
