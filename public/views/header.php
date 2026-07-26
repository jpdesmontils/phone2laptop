<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Phone2Laptop – Transferts instantanés entre téléphone et ordinateur</title>
  <meta name="description" content="Échangez fichiers, photos, vidéos et texte entre smartphone et ordinateur en un seul scan. Gratuit, rapide et sécurisé.">
  <meta name="theme-color" content="#007BFF">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Phone2Laptop">

  <!-- Styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Favicon et PWA -->
  <!-- 
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/icones/icon-32x32.png">
  <link rel="icon" type="image/png" sizes="64x64" href="/assets/icones/icon-64x64.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/icones/icon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/icones/icon-180x180.png">
  <link rel="manifest" href="/manifest.json">
  -->

  <!-- Styles personnalisés -->
  <style>
    body { scroll-behavior: smooth; }
    .hero { background: #f8f9fa; }
    .upload-counter {
      background: #fff;
      border-top: 1px solid #e9ecef;
      box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.04);
    }
    .upload-counter-value { font-variant-numeric: tabular-nums; }
    #file-list img { height: 40px; width: auto; }
    #shared-text { height: 240px; resize: vertical; }
    .step-icon { font-size: 2.5rem; }
  </style>
</head>

<body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<!-- Google tag (gtag.js) -->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-L9YP0EEL7M"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-L9YP0EEL7M');
</script>
<!-- Consent Manager -->
<script type="text/javascript" data-cmp-ab="1" src="https://cdn.consentmanager.net/delivery/autoblocking/7c54cffeb13b0.js" data-cmp-host="d.delivery.consentmanager.net" data-cmp-cdn="cdn.consentmanager.net" data-cmp-codesrc="16"></script>
