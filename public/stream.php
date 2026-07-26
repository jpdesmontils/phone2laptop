<?php
require_once __DIR__ . '/includes/storage.php';

if (empty($_GET['token'])) {
    http_response_code(400);
    exit;
}
$token = normalizeToken($_GET['token']);
if ($token === null) { http_response_code(400); exit; }
$dir = sessionDirectory($token);

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

$etag = '';
while (true) {
    clearstatcache();
    $files = is_dir($dir) ? glob($dir.'/*') : [];
    sort($files);
    $newEtag = md5(json_encode($files));

    if ($newEtag !== $etag) {
        $etag = $newEtag;
        echo "data: update\n\n";
        @ob_flush(); flush();
    }
    sleep(2);   // poll léger
}
