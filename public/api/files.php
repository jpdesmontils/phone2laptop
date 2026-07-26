<?php
header('Content-Type: application/json');

$token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
$root  = dirname(__DIR__, 2);
$dir   = $root.'/uploads/'.$token;

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
		if ($name === 'bloc-notes.txt') continue;
		$list[] = [
			'name' => $name,
			'size' => filesize($f),
			'url'  => $base.rawurlencode($name)          // ← pointe vers download.php
		];
	}
}
echo json_encode($list);
