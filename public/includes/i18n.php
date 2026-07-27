<?php

const SUPPORTED_LANGUAGES = ['fr', 'en'];
const DEFAULT_LANGUAGE = 'fr';

function requested_language(): string
{
    $requested = $_GET['lang'] ?? null;
    if (is_string($requested) && in_array($requested, SUPPORTED_LANGUAGES, true)) {
        setcookie('p2l_language', $requested, [
            'expires' => time() + 31536000,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        return $requested;
    }
    $saved = $_COOKIE['p2l_language'] ?? null;
    if (is_string($saved) && in_array($saved, SUPPORTED_LANGUAGES, true)) return $saved;

    foreach (explode(',', (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')) as $preference) {
        $language = strtolower(substr(trim(explode(';', $preference)[0]), 0, 2));
        if (in_array($language, SUPPORTED_LANGUAGES, true)) return $language;
    }
    return DEFAULT_LANGUAGE;
}

function translations(string $language): array
{
    $directory = dirname(__DIR__) . '/locales/';
    $fallback = json_decode((string) file_get_contents($directory . DEFAULT_LANGUAGE . '.json'), true, 512, JSON_THROW_ON_ERROR);
    if ($language === DEFAULT_LANGUAGE) return $fallback;
    $selected = json_decode((string) file_get_contents($directory . $language . '.json'), true, 512, JSON_THROW_ON_ERROR);
    return array_replace_recursive($fallback, $selected);
}
