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

// [SEGURIDAD] Toda creación exige POST y token CSRF válido.
csrf_exigir_post();

$datos = $_POST;
$errores = servicios_validar_formulario($datos);
$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$nombreImagenNueva = null;

if ($errores === [] && servicios_hay_imagen_nueva($_FILES)) {
    try {
        $nombreImagenNueva = $gestorImagenes->guardar($_FILES['imagen']);
    } catch (ImagenException $exception) {
        $errores['imagen'][] = $exception->getMessage();
    }
}

if ($errores !== []) {
    // [PRG] El POST nunca renderiza el formulario directamente.
    servicios_formulario_guardar('crear', $datos, $errores);
    servicios_redirigir('/servicios/crear.php');
}

try {
    $datos['imagen'] = $nombreImagenNueva;
    $servicio = ServicioFactory::desdeFormulario($datos);
    (new ServicioRepositorio(conexion_obtener()))->crear($servicio);
} catch (InvalidArgumentException $exception) {
    $gestorImagenes->eliminar($nombreImagenNueva);
    servicios_formulario_guardar('crear', $datos, ['_general' => [$exception->getMessage()]]);
    servicios_redirigir('/servicios/crear.php');
} catch (DatabaseException $exception) {
    $gestorImagenes->eliminar($nombreImagenNueva);
    servicios_formulario_guardar('crear', $datos, ['_general' => [DatabaseException::PUBLIC_MESSAGE]]);
    servicios_redirigir('/servicios/crear.php');
}

flash('success', 'Servicio creado correctamente.');
servicios_redirigir('/servicios/index.php');
