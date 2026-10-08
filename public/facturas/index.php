<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use App\PeriodoFacturacion;
use App\Repositories\FacturaRepositorio;

$repositorio = new FacturaRepositorio(conexion_obtener());

$facturas = array_map(
    static function (array $fila): array {
        $periodo = new PeriodoFacturacion(
            new DateTimeImmutable($fila['fecha_inicio']),
            new DateTimeImmutable($fila['fecha_fin'])
        );

        return [
            'id' => $fila['id'],
            'cliente' => $fila['cliente'],
            'periodo' => $periodo->obtenerEtiqueta(),
            'rango' => sprintf(
                '%s al %s',
                $periodo->getInicio()->format('d/m/Y'),
                $periodo->getFin()->format('d/m/Y')
            ),
            'total' => $fila['total'],
        ];
    },
    $repositorio->listar()
);

$raiz = dirname(__DIR__, 2);
$titulo = 'Facturas';

require $raiz . '/views/layout/encabezado.php';
require $raiz . '/views/facturas/listar.php';
require $raiz . '/views/layout/pie.php';
