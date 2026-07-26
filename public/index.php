<?php
/*──────────────────────────────────────────────
  phone2laptop – landing + zone d’échange (SSE)
──────────────────────────────────────────────*/
define('PHONE2LAPTOP_APP', true);
require_once __DIR__ . '/includes/session.php';

// ─── paramètres
$hasParam   = isset($_GET['token']);
$token      = $hasParam ? session_token($_GET['token']) : bin2hex(random_bytes(16));
if (!$token) { http_response_code(400); exit('Session invalide'); }
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath   = rtrim(dirname($_SERVER['PHP_SELF']), '/');
$baseUrl    = $scheme.'://'.$_SERVER['HTTP_HOST'].$basePath;
$link       = $baseUrl.'?token='.$token;
purge_expired_sessions();
$session = open_session($token, !$hasParam);
if (!$session) { http_response_code(410); exit('Cette session a expiré.'); }
$expiresAt = $session['expiresAt'];

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


