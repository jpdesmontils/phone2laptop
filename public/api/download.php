<?php
/* Téléchargement sécurisé avec en-têtes MIME corrects */
if (empty($_GET['token']) || empty($_GET['name'])) { http_response_code(400); exit; }

$token = preg_replace('/[^a-f0-9]/', '', $_GET['token']);
$name  = basename($_GET['name']);                               // protège contre ../
$path  = dirname(__DIR__, 2)."/uploads/$token/$name";

if (!is_file($path)) { http_response_code(404); exit; }

$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) ?: 'application/octet-stream';
header('Content-Type: '.$mime);
header('Content-Length: '.filesize($path));
header('Content-Disposition: attachment; filename="'.$name.'"');
readfile($path);
