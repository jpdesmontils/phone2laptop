<?php


// === Paramètres de base ===
date_default_timezone_set('Europe/Paris'); // ou ta timezone
// Chemin de base de l’application
define('APP_ROOT', __DIR__ . '/..');
// URL de base (utile pour les redirections ou liens absolus)

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BASE_URL', $protocol . '://' . $host . $basePath);

