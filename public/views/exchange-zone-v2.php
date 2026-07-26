<?php



$lang = $_GET['lang'] ?? 'fr';
$texts = loadTexts($lang);


if (!$hasParam && isMobile()) 
{
    include  'mobile.view.php';
} else {
    include  'desktop.view.php';
}

?>