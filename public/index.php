<?php
/*──────────────────────────────────────────────
  phone2laptop – landing + zone d’échange (SSE)
──────────────────────────────────────────────*/
define('PHONE2LAPTOP_APP', true);
include_once	"lib.php";
require_once __DIR__.'/includes/upload-counter.php';
// include_once 	'includes/config.php';
// include_once	'includes/functions.php';

// ─── paramètres
$TTL        = 1800;                                   // 30 minutes
$hasParam   = isset($_GET['token']);
$token      = $hasParam ? $_GET['token'] : bin2hex(random_bytes(16));
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl    = $scheme.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$link       = $baseUrl.'?token='.$token."#share";
$qrUrl      = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='.urlencode($link);

// ─── dossiers
$root        = dirname(__DIR__);
$uploadsRoot = $root.'/uploads';
$sessionDir  = $uploadsRoot.'/'.$token;
@mkdir($sessionDir, 0775, true);
$uploadCount = readUploadCount();

// ─── purge dossiers expirés
if (is_dir($uploadsRoot)) {
    foreach (glob($uploadsRoot.'/*') as $d) {
        if (is_dir($d) && (time() - filemtime($d) > $TTL)) {
            array_map('unlink', glob($d.'/*'));
            @rmdir($d);
        }
    }
}

// render();
// ─── Inclusion des vues
include __DIR__.'/views/header.php';
include __DIR__.'/views/nav.php';
include __DIR__.'/views/hero.php';
include __DIR__.'/views/why.php';
include __DIR__.'/views/exchange-zone.php';
include __DIR__.'/views/testi.php';
include __DIR__.'/views/donate.php';
include __DIR__.'/views/footer.php';


