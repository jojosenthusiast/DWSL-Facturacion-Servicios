<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

use App\Repositories\FacturaRepositorio;

$repositorio = new FacturaRepositorio(conexion_obtener());

$entradas = $repositorio->buscarTodas();

$raiz = dirname(__DIR__, 2);
$titulo = 'Reporte web de facturas';

require $raiz . '/views/layout/cabecera.php';
require $raiz . '/views/reporte/index.php';
require $raiz . '/views/layout/pie.php';
