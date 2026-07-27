<?php
require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/upload-counter.php';
header('Content-Type: application/json');

function upload_error(int $code, string $message)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') upload_error(405, 'method not allowed');
$token = session_token($_POST['token'] ?? '');
if (!$token || empty($_FILES['file'])) upload_error(400, 'missing token or file');
$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) upload_error(400, "upload error ({$file['error']})");
if ($file['size'] > 50 * 1024 * 1024 + 4096) upload_error(413, 'file too large');

$session = open_session($token);
if (!$session) upload_error(410, 'session expired');
$safeName = basename($file['name']);
if (!preg_match('/^[a-f0-9-]{36}\.enc$/', $safeName)) upload_error(400, 'invalid encrypted filename');
$destination = $session['dir'] . '/' . $safeName;
if (file_exists($destination)) upload_error(409, 'encrypted file already exists');
if (!move_uploaded_file($file['tmp_name'], $destination)) upload_error(500, 'cannot move file');

$renewed = renew_session($session['dir']);
if (!$renewed) {
    unlink($destination);
    upload_error(500, 'cannot renew session');
}
incrementUploadCount();
echo json_encode(['ok' => true, 'name' => $safeName, 'size' => $file['size'], 'expiresAt' => $renewed['expiresAt'] * 1000]);
