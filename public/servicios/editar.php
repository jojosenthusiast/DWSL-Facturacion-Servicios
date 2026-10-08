<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Factories\ServicioFactory;
use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

$id = servicios_id_desde_peticion($_GET['id'] ?? null);
if ($id === null) {
    servicios_404();
}

$servicio = (new ServicioRepositorio(conexion_obtener()))->buscarPorId($id);
if ($servicio === null) {
    servicios_404();
}

$estado = servicios_formulario_extraer('editar_' . $id);
$valores = $estado['valores'] ?? servicios_valores_modelo($servicio);
$errores = $estado['errores'] ?? [];
$tipos = ServicioFactory::tiposDisponibles();
$camposPorTipo = ServicioFactory::camposPorTipo();
$accion = '/servicios/actualizar.php?id=' . $id;
$textoBoton = 'Actualizar servicio';
$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$imagenActualUrl = servicios_imagen_url($gestorImagenes, $servicio->getImagen());
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar servicio</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<main class="contenedor">
    <p><a href="/servicios/ver.php?id=<?= e($id) ?>">← Volver al detalle</a></p>
    <h1>Editar servicio</h1>
    <?php require __DIR__ . '/_formulario.php'; ?>
</main>
</body>
</html>
