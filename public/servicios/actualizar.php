<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\ImagenException;
use App\Factories\ServicioFactory;
use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    servicios_405();
}

// [SEGURIDAD] La modificación nunca se ejecuta por GET.
csrf_exigir_post();
$id = servicios_id_desde_peticion($_GET['id'] ?? null);
if ($id === null) {
    servicios_404();
}

$repositorio = new ServicioRepositorio(conexion_obtener());
$actual = $repositorio->buscarPorId($id);
if ($actual === null) {
    servicios_404();
}

$datos = $_POST;
$errores = servicios_validar_formulario($datos);
$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$imagenAnterior = $actual->getImagen();
$imagenNueva = null;

if ($errores === [] && servicios_hay_imagen_nueva($_FILES)) {
    try {
        // Se guarda primero, pero la anterior solo se elimina después de confirmar la BD.
        $imagenNueva = $gestorImagenes->guardar($_FILES['imagen']);
    } catch (ImagenException $exception) {
        $errores['imagen'][] = $exception->getMessage();
    }
}

if ($errores !== []) {
    servicios_formulario_guardar('editar_' . $id, $datos, $errores);
    servicios_redirigir('/servicios/editar.php?id=' . $id);
}

try {
    $datos['imagen'] = $imagenNueva ?? $imagenAnterior;
    $servicio = ServicioFactory::desdeFormulario($datos);
    $servicio->setIdBd($id);
    $repositorio->actualizar($servicio);
} catch (InvalidArgumentException $exception) {
    $gestorImagenes->eliminar($imagenNueva);
    servicios_formulario_guardar('editar_' . $id, $datos, ['_general' => [$exception->getMessage()]]);
    servicios_redirigir('/servicios/editar.php?id=' . $id);
} catch (DatabaseException $exception) {
    $gestorImagenes->eliminar($imagenNueva);
    servicios_formulario_guardar('editar_' . $id, $datos, ['_general' => [DatabaseException::PUBLIC_MESSAGE]]);
    servicios_redirigir('/servicios/editar.php?id=' . $id);
}

if ($imagenNueva !== null && $imagenAnterior !== $imagenNueva) {
    $gestorImagenes->eliminar($imagenAnterior);
}

flash('success', 'Servicio actualizado correctamente.');
servicios_redirigir('/servicios/ver.php?id=' . $id);
