<?php
/*───────────────────────────────────────────
  API : upload de fichier
───────────────────────────────────────────*/
require_once __DIR__ . '/../includes/storage.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method not allowed']);
    exit;
}

/*── helpers ───────────────────────────────*/
function fail(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

/*── récup. paramètres ─────────────────────*/
$token = normalizeToken($_POST['token'] ?? null);
if ($token === null || empty($_FILES['file'])) {
    fail(400, 'missing token or file');
}

$file = $_FILES['file'];


/*── contrôles de base ─────────────────────*/
if ($file['error'] !== UPLOAD_ERR_OK) {
	$err = $file['error'];
	if ($err !== UPLOAD_ERR_OK) {
		$map = [
			1   =>  'Le fichier dépasse la limite serveur (php.ini).',
			2  	=>  'Le fichier dépasse la limite du formulaire HTML.',
			3   =>  'Le fichier n’a été que partiellement téléversé.',
			4   =>  'Aucun fichier n’a été envoyé.',
			6 	=>  'Dossier temporaire manquant sur le serveur.',
			7 	=>  'Impossible d’écrire le fichier sur le disque.',
			8  	=>  'Téléversement stoppé par une extension PHP.'];

		fail(500, $map[$err] );  
	}
	// fail(500, "Erreur de type [$err]" );          // fail() = helper qui renvoie JSON + code HTTP
	// }
}


$MAX_SIZE = 50 * 1024 * 1024;                       // 50 Mo
if ($file['size'] > $MAX_SIZE) {
    fail(413, 'file too large');
}

/*── validation MIME ───────────────────────*/
$allowed = [
    // images
    'image/png', 'image/jpeg', 'image/webp', 'image/heic', 'image/heif', 'image/jxl',
    // vidéos
    'video/mp4', 'video/quicktime', 'video/3gpp', 'video/3gpp2', 'video/webm',
    // Documents
    'application/pdf',
    'text/plain',
    'application/msword', // .doc
    'application/vnd.ms-excel', // .xls
    'application/vnd.ms-powerpoint', // .ppt
    // Office Open XML (formats modernes)
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',       // .xlsx ← ajouté
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
];
$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) ?: 'application/octet-stream';
if (!in_array($mime, $allowed, true)) {
    fail(415, 'mime not allowed');
}

/*── dossier de session ────────────────────*/
$dir = sessionDirectory($token);
if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    fail(500, 'cannot create dir');
}

/*── nom de fichier sûr ────────────────────*/
$rawName = basename($file['name']);                 // nom original
$safeName = preg_replace('/[^\w\-.]/', '_', $rawName); // nettoie (garde l’ext)
$dest = $dir . '/' . $safeName;

/*── déplacement ───────────────────────────*/
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    fail(500, 'cannot move file');
}

/*── réponse OK ────────────────────────────*/
echo json_encode([
    'ok'   => true,
    'name' => $safeName,
    'size' => $file['size'],
    'mime' => $mime
]);
