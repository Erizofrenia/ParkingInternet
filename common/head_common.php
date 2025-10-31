<?php
// head_common.php

// Datos básicos del sitio
$siteTitle = "Parking Internet - Sistema de Gestión de Estacionamiento";
$siteDescription = "Sistema integral de gestión de estacionamiento con control de entradas y salidas, gestión de tarifas y tickets automáticos.";
$siteURL = "https://parqueo.hackersinternet.mx"; 
$siteImage = $siteURL . "/media/parkinginternet.png"; // Ruta absoluta accesible desde la web
?>

<!-- Charset y viewport -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Título de la página -->
<title><?= htmlspecialchars($siteTitle) ?></title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="<?= $siteImage ?>" sizes="32x32">
<link rel="shortcut icon" href="<?= $siteImage ?>" type="image/png">

<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:title" content="<?= htmlspecialchars($siteTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($siteDescription) ?>">
<meta property="og:image" content="<?= $siteImage ?>">
<meta property="og:url" content="<?= $siteURL ?>">
<meta property="og:type" content="website">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($siteTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($siteDescription) ?>">
<meta name="twitter:image" content="<?= $siteImage ?>">
