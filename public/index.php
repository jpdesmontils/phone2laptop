<?php
define('PHONE2LAPTOP_APP', true);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/content.php';

$language = selected_language();
$content = page_content($language);
$hasParam = isset($_GET['token']);
$token = $hasParam ? session_token($_GET['token']) : bin2hex(random_bytes(16));
if (!$token) { http_response_code(400); exit('Invalid session'); }
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/');
$languageQuery = '&lang=' . $language;
$link = $baseUrl . '/?token=' . $token . $languageQuery;
purge_expired_sessions();
$session = open_session($token, !$hasParam);
if (!$session) { http_response_code(410); exit($language === 'fr' ? 'Cette session a expiré.' : 'This session has expired.'); }
$expiresAt = $session['expiresAt'];
$studioUrl = 'https://solenis.studio?utm_source=phone2laptop&utm_medium=product&utm_campaign=product_showcase';
$donationUrl = 'https://donate.stripe.com/4gM5kE2z69M21qFbDAew807';
$alternateLanguage = $language === 'fr' ? 'en' : 'fr';
$query = $_GET;
$query['lang'] = $alternateLanguage;
$languageUrl = '?' . http_build_query($query) . ($hasParam && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#') !== false ? '#share' : '');
$context = array_replace_recursive($content, [
    'sessionUrl' => '?token=' . $token . $languageQuery . '#share',
    'shareUrl' => $link,
    'studioUrl' => $studioUrl,
    'donationUrl' => $donationUrl,
    'tagline' => $language === 'fr' ? 'Le raccourci privé entre vos appareils.' : 'The private shortcut between your devices.'
]);
?><!doctype html>
<html lang="<?= $language ?>">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <title><?= htmlspecialchars($content['meta']['title']) ?></title>
 <meta name="description" content="<?= htmlspecialchars($content['meta']['description']) ?>"><meta name="theme-color" content="#3157e8">
 <link rel="icon" href="<?= $baseUrl ?>/favicon.ico"><link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 <link rel="stylesheet" href="<?= $baseUrl ?>/assets/site.css">
 <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'WebApplication', 'name' => 'Phone2Laptop', 'applicationCategory' => 'UtilitiesApplication', 'operatingSystem' => 'Any modern browser', 'description' => $content['meta']['description'], 'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head><body>
<a class="skip-link" href="#main-content"><?= $language === 'fr' ? 'Aller au contenu' : 'Skip to content' ?></a>
<nav class="site-nav" aria-label="<?= $language === 'fr' ? 'Navigation principale' : 'Main navigation' ?>"><div class="container nav-inner"><a class="brand" href="<?= $baseUrl ?>/?lang=<?= $language ?>"><img src="<?= $baseUrl ?>/assets/images/icon.svg" alt="">Phone2Laptop</a><div class="nav-links"><a href="<?= $hasParam ? $baseUrl . '/?lang=' . $language : '' ?>#how"><?= htmlspecialchars($content['nav']['how']) ?></a><a href="<?= $hasParam ? $baseUrl . '/?lang=' . $language : '' ?>#security"><?= htmlspecialchars($content['nav']['security']) ?></a><a href="<?= htmlspecialchars($studioUrl) ?>" target="_blank" rel="noopener" data-event="solenis_cta_clicked"><?= htmlspecialchars($content['nav']['studio']) ?></a><a class="language" href="<?= htmlspecialchars($languageUrl) ?>" hreflang="<?= $alternateLanguage ?>"><?= htmlspecialchars($content['nav']['language']) ?></a></div></div></nav>
<?php
render_template($hasParam ? 'exchange' : 'landing', $context);
?>
<footer class="site-footer"><div class="container"><div class="footer-grid"><a class="brand" href="<?= $baseUrl ?>/?lang=<?= $language ?>"><img src="<?= $baseUrl ?>/assets/images/icon.svg" alt="">Phone2Laptop</a><div><div class="footer-links"><a href="<?= htmlspecialchars($studioUrl) ?>" target="_blank" rel="noopener" data-event="solenis_cta_clicked"><?= htmlspecialchars($content['nav']['studio']) ?></a><a href="<?= htmlspecialchars($donationUrl) ?>" target="_blank" rel="noopener" data-event="donation_cta_clicked"><?= htmlspecialchars($content['footer']['support']) ?></a></div><p class="footer-note"><?= htmlspecialchars($content['footer']['notice']) ?></p></div></div><p class="footer-note">© <?= date('Y') ?> Solenis Studio</p></div></footer>
<script>window.P2LAnalytics={track:function(name){window.dispatchEvent(new CustomEvent('p2l:analytics',{detail:{event:name}}));}};document.querySelectorAll('[data-event]').forEach(function(el){el.addEventListener('click',function(){window.P2LAnalytics.track(el.dataset.event);});});window.P2LAnalytics.track('<?= $hasParam ? 'qr_code_scanned' : 'landing_view' ?>');<?= $hasParam ? "window.P2LAnalytics.track('device_connected');" : '' ?></script>
<?php if ($hasParam): ?>
<script>window.P2L=<?= json_encode(['token' => $token, 'apiBase' => $baseUrl . '/api/', 'baseUrl' => $baseUrl, 'shareUrl' => $link, 'expiresAt' => $expiresAt === null ? null : $expiresAt * 1000, 'language' => $language], JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script><script src="<?= $baseUrl ?>/js/sync.js"></script>
<?php endif; ?></body></html>
