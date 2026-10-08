<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

$repositorio = new ServicioRepositorio(conexion_obtener());
$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$servicios = $repositorio->listar();
$mensajeExito = flash_obtener('success');
$mensajeError = flash_obtener('error');
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Servicios</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<main class="contenedor contenedor-ancho">
    <header class="cabecera-pagina">
        <div><h1>Servicios</h1><p>Administración de servicios facturables.</p></div>
        <a class="boton" href="/servicios/crear.php">Crear servicio</a>
    </header>

    <?php require dirname(__DIR__, 2) . '/views/partials/mensajes.php'; ?>

    <?php if ($servicios === []): ?>
        <div class="vacio"><p>No hay servicios registrados.</p><a href="/servicios/crear.php">Registrar el primero</a></div>
    <?php else: ?>
        <div class="tabla-responsive">
            <table>
                <thead><tr><th>Imagen</th><th>Nombre</th><th>Tipo</th><th>Importe</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><img class="miniatura" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt=""></td>
                        <td><strong><?= e($servicio->getNombre()) ?></strong><br><small><?= e($servicio->getCodigo()) ?></small></td>
                        <td><?= e($servicio->tipoLegible()) ?></td>
                        <td>$<?= e(number_format($servicio->calcularImporte(), 2)) ?></td>
                        <td><?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?></td>
                        <td class="acciones-tabla">
                            <a href="/servicios/ver.php?id=<?= e($servicio->getIdBd()) ?>">Ver</a>
                            <a href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar</a>
                            <a class="enlace-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
