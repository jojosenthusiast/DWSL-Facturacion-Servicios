<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

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

$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$datosEspecificos = $servicio->datosEspecificos();
$camposEspecificos = $servicio->camposEspecificos();
$mensajeExito = flash_obtener('success');
$mensajeError = flash_obtener('error');
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($servicio->getNombre()) ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<main class="contenedor">
    <p><a href="/servicios/index.php">← Volver a servicios</a></p>
    <?php if ($mensajeExito !== null): ?><p class="alerta alerta-exito" role="status"><?= e($mensajeExito) ?></p><?php endif; ?>
    <?php if ($mensajeError !== null): ?><p class="alerta alerta-error" role="alert"><?= e($mensajeError) ?></p><?php endif; ?>
    <div class="detalle-servicio">
        <img class="imagen-detalle" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt="Imagen de <?= e($servicio->getNombre()) ?>">
        <div>
            <h1><?= e($servicio->getNombre()) ?></h1>
            <dl class="lista-datos">
                <dt>Código</dt><dd><?= e($servicio->getCodigo()) ?></dd>
                <dt>Tipo</dt><dd><?= e($servicio->tipoLegible()) ?></dd>
                <dt>Estado</dt><dd><?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?></dd>
                <!-- [POLIMORFISMO] El detalle consume la API del modelo sin reconocer tipos concretos. -->
                <?php foreach ($datosEspecificos as $campo => $valor): ?>
                    <dt><?= e($camposEspecificos[$campo]['etiqueta'] ?? $campo) ?></dt><dd><?= e($valor) ?></dd>
                <?php endforeach; ?>
                <dt>Importe</dt><dd><strong>$<?= e(number_format($servicio->calcularImporte(), 2)) ?></strong></dd>
            </dl>
            <div class="acciones-formulario">
                <a class="boton" href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar</a>
                <a class="boton-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar</a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
