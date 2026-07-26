<?php

function isMobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileKeywords = [
        'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry',
        'IEMobile', 'Opera Mini', 'Opera Mobi', 'Windows Phone',
        'BB10', 'PlayBook', 'Mobile Safari', 'webOS', 'Kindle', 'Silk'
    ];

    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

function loadTexts($lang = 'fr') {
    $validLangs = ['fr', 'en'];
    if (!in_array($lang, $validLangs)) $lang = 'fr';

    $path = __DIR__ . "/texts/{$lang}.static";
    if (!file_exists($path)) {
        throw new Exception("Fichier de langue introuvable : {$lang}");
    }

    $content = file_get_contents($path);
    return json_decode($content, true);
}