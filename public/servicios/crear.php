<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Factories\ServicioFactory;

$estado = servicios_formulario_extraer('crear');
$valores = $estado['valores'] ?? ['tipo' => '', 'activo' => '1'];
$errores = $estado['errores'] ?? [];
$tipos = ServicioFactory::tiposDisponibles();
$camposPorTipo = ServicioFactory::camposPorTipo();
$accion = '/servicios/guardar.php';
$textoBoton = 'Guardar servicio';
$imagenActualUrl = null;
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear servicio</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<main class="contenedor">
    <p><a href="/servicios/index.php">← Volver a servicios</a></p>
    <h1>Crear servicio</h1>
    <?php require __DIR__ . '/_formulario.php'; ?>
</main>
</body>
</html>
