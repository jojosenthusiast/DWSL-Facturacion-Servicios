<?php

declare(strict_types=1);

$mensajeExito = flash_obtener('success');
$mensajeError = flash_obtener('error');
$nombreSistema = 'FacturaciÃ³n de servicios';
$tituloPagina = $titulo ?? $nombreSistema;
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$enlaces = [
    '/' => 'Inicio',
    '/servicios/index.php' => 'Servicios',
    '/facturas/index.php' => 'Facturas',
    '/reporte/index.php' => 'Reporte',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> | <?= e($nombreSistema) ?></title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<a class="salto-contenido" href="#contenido-principal">Saltar al contenido principal</a>
<header class="cabecera-sitio">
    <div class="contenedor">
        <p class="nombre-sistema"><a href="/"><?= e($nombreSistema) ?></a></p>
        <nav class="navegacion" aria-label="NavegaciÃ³n principal">
            <?php foreach ($enlaces as $ruta => $etiqueta): ?>
                <?php $activo = $ruta === '/' ? $rutaActual === '/' : str_starts_with($rutaActual, dirname($ruta) . '/'); ?>
                <a href="<?= e($ruta) ?>"<?= $activo ? ' aria-current="page"' : '' ?>><?= e($etiqueta) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<main id="contenido-principal" class="contenedor">
    <h1><?= e($tituloPagina) ?></h1>
    <?php require dirname(__DIR__) . '/partials/mensajes.php'; ?>
