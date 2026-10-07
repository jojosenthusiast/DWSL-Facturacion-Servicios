<?php

declare(strict_types=1);

$mensajeExito = flash_obtener('success');
$mensajeError = flash_obtener('error');
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Facturación de servicios') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<main class="contenedor">
    <nav class="navegacion" aria-label="Secciones">
        <a href="/">Inicio</a>
        <a href="/facturas/index.php">Facturas</a>
        <a href="/facturas/crear.php">Nueva factura</a>
        <a href="/reporte/index.php">Reporte web</a>
    </nav>
    <h1><?= e($titulo ?? '') ?></h1>
    <?php if ($mensajeExito !== null): ?>
        <p class="mensaje" role="status"><?= e($mensajeExito) ?></p>
    <?php endif; ?>
    <?php if ($mensajeError !== null): ?>
        <p class="mensaje mensaje--error" role="alert"><?= e($mensajeError) ?></p>
    <?php endif; ?>
