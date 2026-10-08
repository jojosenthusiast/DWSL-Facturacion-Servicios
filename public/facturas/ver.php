<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use App\Repositories\FacturaRepositorio;
use App\Validation\Validador;

$raiz = dirname(__DIR__, 2);

$responderAviso = static function (int $codigo, string $titulo, string $aviso) use ($raiz): never {
    http_response_code($codigo);
    require $raiz . '/views/layout/encabezado.php';
    require $raiz . '/views/partials/aviso.php';
    require $raiz . '/views/layout/pie.php';
    exit;
};

$idRecibido = $_GET['id'] ?? null;
$validador = (new Validador())->enteroPositivo('id', $idRecibido);
if (!$validador->esValido()) {
    $responderAviso(400, 'Solicitud no válida', 'El número de factura no es válido.');
}

$id = (int) $idRecibido;

$repositorio = new FacturaRepositorio(conexion_obtener());

$factura = $repositorio->buscarPorId($id);
if ($factura === null) {
    $responderAviso(404, 'Factura no encontrada', sprintf('No existe ninguna factura con el número %d.', $id));
}

$periodo = $factura->obtenerPeriodo();
$rango = sprintf(
    '%s al %s',
    $periodo->getInicio()->format('d/m/Y'),
    $periodo->getFin()->format('d/m/Y')
);

$titulo = sprintf('Factura #%d', $id);

require $raiz . '/views/layout/encabezado.php';
require $raiz . '/views/facturas/ver.php';
require $raiz . '/views/layout/pie.php';
