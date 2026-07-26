<?php
/*───────────────────────────────────────────
  API : upload de fichier
───────────────────────────────────────────*/
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method not allowed']);
    exit;
}

/*── helpers ───────────────────────────────*/
function fail(int $code, string $msg) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

/*── récup. paramètres ─────────────────────*/
$token = session_token($_POST['token'] ?? '');
if (!$token || empty($_FILES['file'])) {
    fail(400, 'missing token or file');
}

$file = $_FILES['file'];


/*── contrôles de base ─────────────────────*/
if ($file['error'] !== UPLOAD_ERR_OK) {
    $err = $file['error'];
    $map = [
        1 => 'Le fichier dépasse la limite serveur (php.ini).',
        2 => 'Le fichier dépasse la limite du formulaire HTML.',
        3 => 'Le fichier n’a été que partiellement téléversé.',
        4 => 'Aucun fichier n’a été envoyé.',
        6 => 'Dossier temporaire manquant sur le serveur.',
        7 => 'Impossible d’écrire le fichier sur le disque.',
        8 => 'Téléversement stoppé par une extension PHP.',
    ];
    fail(500, $map[$err] ?? "Erreur de téléversement ($err)");
}


$MAX_SIZE = 50 * 1024 * 1024 + 4096;                // 50 Mo de contenu + enveloppe chiffrée
if ($file['size'] > $MAX_SIZE) {
    fail(413, 'file too large');
}

/*── dossier de session ────────────────────*/
$session = open_session($token);
if (!$session) fail(410, 'session expired');
$dir = $session['dir'];

/*── nom de fichier sûr ────────────────────*/
$safeName = basename($file['name']);
if (!preg_match('/^[a-f0-9-]{36}\.enc$/', $safeName)) fail(400, 'invalid encrypted filename');
$dest = $dir . '/' . $safeName;
if (file_exists($dest)) fail(409, 'encrypted file already exists');

/*── déplacement ───────────────────────────*/
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    fail(500, 'cannot move file');
}
$session = renew_session($dir);
if (!$session) {
    unlink($dest);
    fail(500, 'cannot renew session');
}

/*── réponse OK ────────────────────────────*/
echo json_encode([
    'ok'   => true,
    'name' => $safeName,
    'size' => $file['size'],
    'mime' => 'application/octet-stream',
    'expiresAt' => $session['expiresAt'] * 1000
]);
