<?php
require_once __DIR__ . '/../includes/storage.php';

header('Content-Type: application/json');

$token = normalizeToken($_GET['token'] ?? null);
if ($token === null) { http_response_code(400); echo json_encode(['error' => 'Token invalide']); exit; }
$dir = sessionDirectory($token);

$list  = [];
if (is_dir($dir)) {
    $files = glob($dir.'/*');
    sort($files);
	$base = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http')
		  .'://'.$_SERVER['HTTP_HOST']
		  .dirname(dirname($_SERVER['PHP_SELF'])).'/api/download.php?token='.$token.'&name=';

	foreach ($files as $f) {
		if (!is_file($f)) continue;
		$name = basename($f);
		$list[] = [
			'name' => $name,
			'size' => filesize($f),
			'url'  => $base.rawurlencode($name)          // ← pointe vers download.php
		];
	}
}
echo json_encode($list);
