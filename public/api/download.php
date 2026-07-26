<?php
/* Téléchargement sécurisé avec en-têtes MIME corrects */
require_once __DIR__ . '/../includes/storage.php';

if (empty($_GET['token']) || empty($_GET['name'])) { http_response_code(400); exit; }

$token = normalizeToken($_GET['token']);
if ($token === null) { http_response_code(400); exit; }
$name  = basename($_GET['name']);                               // protège contre ../
$path  = sessionDirectory($token)."/$name";

if (!is_file($path)) { http_response_code(404); exit; }

$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) ?: 'application/octet-stream';
header('Content-Type: '.$mime);
header('Content-Length: '.filesize($path));
header('Content-Disposition: attachment; filename="'.$name.'"');
readfile($path);
