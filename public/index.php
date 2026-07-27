<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/upload-counter.php';
require_once __DIR__ . '/includes/mustache.php';
require_once __DIR__ . '/includes/i18n.php';

$language = requested_language();
$text = translations($language);

$hasParam = isset($_GET['token']);
$token = $hasParam ? session_token($_GET['token']) : bin2hex(random_bytes(16));
if (!$token) { http_response_code(400); exit(htmlspecialchars($text['errors']['invalid_session'])); }

purge_expired_sessions();
$session = open_session($token, true);
if (!$session) { http_response_code(500); exit(htmlspecialchars($text['errors']['create_session'])); }

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath;
$link = $baseUrl . '/?token=' . rawurlencode($token);
$query = $hasParam ? '?token=' . rawurlencode($token) . '&lang=' : '?lang=';

$context = $text + [
    'locale_code' => $language,
    'is_fr' => $language === 'fr',
    'is_en' => $language === 'en',
    'landing' => !$hasParam,
    'base_url' => $baseUrl,
    'start_url' => $baseUrl . '/?token=' . rawurlencode($token),
    'fr_url' => $baseUrl . '/' . $query . 'fr',
    'en_url' => $baseUrl . '/' . $query . 'en',
    'upload_count' => number_format(readUploadCount(), 0, $language === 'fr' ? ',' : '.', '&nbsp;'),
    'year' => date('Y'),
    'p2l_config' => json_encode([
        'token' => $token,
        'apiBase' => $baseUrl . '/api/',
        'shareUrl' => $link,
        'expiresAt' => $session['expiresAt'] === null ? null : $session['expiresAt'] * 1000,
        'i18n' => $text['js'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
];

header('Content-Language: ' . $language);
echo render_template('page', $context);
