<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require dirname(__DIR__, 2) . '/src/Web/formulario.php';

use App\Repositories\FacturaRepositorio;

$repositorio = new FacturaRepositorio(conexion_obtener());

$clientes = $repositorio->listarClientes();
$servicios = $repositorio->listarServiciosActivos();

$formulario = formulario_obtener();
$valores = $formulario['datos'];
$errores = $formulario['errores'];

$raiz = dirname(__DIR__, 2);
$titulo = 'Nueva factura';

require $raiz . '/views/layout/encabezado.php';
require $raiz . '/views/facturas/crear.php';
require $raiz . '/views/layout/pie.php';
