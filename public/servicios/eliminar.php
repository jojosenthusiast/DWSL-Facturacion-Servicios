<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Exceptions\DatabaseException;
use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

$id = servicios_id_desde_peticion($_GET['id'] ?? null);
if ($id === null) {
    servicios_404();
}

$repositorio = new ServicioRepositorio(conexion_obtener());
$servicio = $repositorio->buscarPorId($id);
if ($servicio === null) {
    servicios_404();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // [SEGURIDAD] El GET de esta ruta solo confirma; el borrado exige POST + CSRF.
    csrf_exigir_post();
    try {
        $eliminado = $repositorio->eliminar($id);
    } catch (DatabaseException $exception) {
        flash('error', 'No se pudo eliminar el servicio. Puede estar asociado a una factura.');
        servicios_redirigir('/servicios/ver.php?id=' . $id);
    }

    if ($eliminado) {
        (new GestorImagenes(dirname(__DIR__) . '/uploads'))->eliminar($servicio->getImagen());
        flash('success', 'Servicio eliminado correctamente.');
    }

    servicios_redirigir('/servicios/index.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    servicios_405();
}
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eliminar servicio</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<main class="contenedor">
    <p><a href="/servicios/ver.php?id=<?= e($id) ?>">← Cancelar</a></p>
    <h1>Eliminar servicio</h1>
    <div class="alerta alerta-error">
        <p>¿Confirmas que deseas eliminar <strong><?= e($servicio->getNombre()) ?></strong>?</p>
        <p>Esta acción no se puede deshacer.</p>
    </div>
    <form method="post" action="/servicios/eliminar.php?id=<?= e($id) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <button class="boton-peligro" type="submit">Sí, eliminar</button>
        <a class="boton-secundario" href="/servicios/ver.php?id=<?= e($id) ?>">No, cancelar</a>
    </form>
</main>
</body>
</html>
