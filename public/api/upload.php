<?php
/*───────────────────────────────────────────
  API : upload de fichier
───────────────────────────────────────────*/
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
$token = preg_replace('/[^a-f0-9]/', '', $_POST['token'] ?? '');
if (!$token || empty($_FILES['file'])) {
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
    'image/png', 'image/jpeg', 'image/webp', 'image/avif', 'image/heic', 'image/heif', 'image/jxl',
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
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
if ($finfo) finfo_close($finfo);
$mime = $mime ?: 'application/octet-stream';

// Certaines bases MIME serveur anciennes ne reconnaissent pas HEIC/HEIF/AVIF et
// renvoient application/octet-stream. Le repli vérifie aussi la signature ISO
// BMFF du fichier afin de ne pas autoriser un binaire sur sa seule extension.
if ($mime === 'application/octet-stream') {
    $mobileImageMimes = [
        'avif' => 'image/avif',
        'heic' => 'image/heic',
        'heif' => 'image/heif',
    ];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $header = file_get_contents($file['tmp_name'], false, null, 0, 32) ?: '';
    $isIsoBmffImage = strlen($header) >= 12
        && substr($header, 4, 4) === 'ftyp'
        && preg_match('/avif|avis|heic|heix|hevc|hevx|mif1|msf1/', substr($header, 8));
    if (isset($mobileImageMimes[$extension]) && $isIsoBmffImage) {
        $mime = $mobileImageMimes[$extension];
    }
}
if (!in_array($mime, $allowed, true)) {
    fail(415, 'mime not allowed');
}

/*── dossier de session ────────────────────*/
$root = dirname(__DIR__, 2);                        // remonte à /project
$dir  = $root . '/uploads/' . $token;
if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    fail(500, 'cannot create dir');
}

/*── nom de fichier sûr ────────────────────*/
$rawName = basename($file['name']);                 // nom original
$safeName = preg_replace('/[^\w\-.]/', '_', $rawName); // nettoie (garde l’ext)
$dest = $dir . '/' . $safeName;

/*── évite d'écraser un fichier existant du même nom ──*/
if (file_exists($dest)) {
    $ext  = pathinfo($safeName, PATHINFO_EXTENSION);
    $base = pathinfo($safeName, PATHINFO_FILENAME);
    $i = 1;
    do {
        $safeName = $ext !== '' ? "{$base}_{$i}.{$ext}" : "{$base}_{$i}";
        $dest = $dir . '/' . $safeName;
        $i++;
    } while (file_exists($dest));
}

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
