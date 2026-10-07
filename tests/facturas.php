<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\Conexion;
use App\Factura;
use App\PeriodoFacturacion;
use App\Repositories\FacturaRepositorio;
use App\Servicios\Servicio;

$dsn = getenv('DWSL_TEST_DSN');
$usuario = getenv('DWSL_TEST_USER');
$clave = getenv('DWSL_TEST_PASSWORD');
if ($dsn === false || $usuario === false || $clave === false) {
    fwrite(STDERR, "Define DWSL_TEST_DSN, DWSL_TEST_USER y DWSL_TEST_PASSWORD para ejecutar la prueba.\n");
    exit(2);
}

$pdo = (new Conexion(['dsn' => $dsn, 'usuario' => $usuario, 'clave' => $clave]))->obtenerPDO();
$repositorio = new FacturaRepositorio($pdo);

$assert = static function (bool $condicion, string $mensaje): void {
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
    fwrite(STDOUT, "OK: {$mensaje}\n");
};
$rechaza = static function (callable $accion): bool {
    try {
        $accion();
    } catch (InvalidArgumentException) {
        return true;
    }
    return false;
};

$clientes = $repositorio->listarClientes();
$assert(count($clientes) >= 2, 'El seed necesita al menos dos clientes.');
$porCodigo = array_column($repositorio->listarServiciosActivos(), 'id', 'codigo');
$idsServicios = [$porCodigo['SRV-AGUA'], $porCodigo['SRV-INTERNET'], $porCodigo['SRV-RECOLECCION']];

$clienteId = (int) $clientes[0]['id'];
$otroClienteId = (int) $clientes[1]['id'];

$periodo = null;
for ($anio = 2031; $anio <= 2034 && $periodo === null; $anio++) {
    for ($mes = 1; $mes <= 12 && $periodo === null; $mes++) {
        $inicio = new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mes));
        $fin = $inicio->modify('last day of this month');
        $ocupado = $repositorio->existePeriodo($clienteId, $inicio, $fin)
            || $repositorio->existePeriodo($otroClienteId, $inicio, $fin);
        if (!$ocupado) {
            $periodo = new PeriodoFacturacion($inicio, $fin);
        }
    }
}
if (!$periodo instanceof PeriodoFacturacion) {
    throw new RuntimeException('No se encontró un período libre para ejecutar la prueba.');
}

$mesSiguiente = $periodo->getInicio()->modify('first day of next month');
$periodoSiguiente = new PeriodoFacturacion($mesSiguiente, $mesSiguiente->modify('last day of this month'));
$mesPosterior = $periodo->getInicio()->modify('first day of +2 month');
$periodoPosterior = new PeriodoFacturacion($mesPosterior, $mesPosterior->modify('last day of this month'));

$pdo->beginTransaction();
try {
    $facturaId = $repositorio->crear($clienteId, $periodo, $idsServicios);
    $assert($facturaId > 0, 'crear devuelve el id de la factura nueva.');

    $detalles = $repositorio->obtenerDetalles($facturaId);
    $assert(count($detalles) === 3, 'La factura guarda un detalle por servicio.');
    $assert(str_contains((string) $detalles[0]['descripcion'], '(medido)'), 'El detalle guarda la descripción generada por el servicio.');

    $factura = $repositorio->buscarPorId($facturaId);
    $assert($factura instanceof Factura, 'buscarPorId reconstruye la factura del dominio.');
    $lineas = $factura->obtenerLineas();
    $assert(count($lineas) === 3, 'La factura reconstruida expone tres líneas.');
    $assert(abs($factura->calcularTotal() - array_sum(array_column($lineas, 'importe'))) < 0.0001, 'El total coincide con la suma de los servicios.');
    $assert(abs($factura->calcularTotal() - 74.625) < 0.001, 'El importe recalculado es el esperado según el seed.');
    $assert($factura->obtenerPeriodo()->obtenerEtiqueta() === $periodo->obtenerEtiqueta(), 'El período se reconstruye desde la base de datos.');
    $assert($factura->obtenerCliente()->getId() === $clientes[0]['codigo'], 'El cliente se reconstruye desde la base de datos.');

    $facturables = $factura->obtenerFacturables();
    $assert(($facturables[0] ?? null) instanceof Servicio && $facturables[0]->getIdBd() === (int) $porCodigo['SRV-AGUA'], 'El servicio reconstruido conserva el ID de la base de datos.');

    $sumaGuardada = array_sum(array_map(static fn (array $detalle): float => (float) $detalle['importe'], $detalles));
    $assert(abs($sumaGuardada - (float) sprintf('%.2f', $factura->calcularTotal())) < 0.0001, 'Los importes guardados suman el total de la factura.');

    $sumaMostrada = array_sum(array_map(static fn (array $linea): float => (float) sprintf('%.2f', $linea['importe']), $lineas));
    $assert(sprintf('%.2f', $sumaMostrada) === sprintf('%.2f', $factura->calcularTotal()), 'El total mostrado coincide con la suma de los importes mostrados.');

    $assert($repositorio->existePeriodo($clienteId, $periodo->getInicio(), $periodo->getFin()), 'existePeriodo detecta el período ya facturado.');
    $assert($rechaza(static fn () => $repositorio->crear($clienteId, $periodo, $idsServicios)), 'crear rechaza un período duplicado para el mismo cliente.');
    $assert(!$repositorio->existePeriodo($otroClienteId, $periodo->getInicio(), $periodo->getFin()), 'El período no está facturado para otro cliente.');

    $facturaOtroCliente = $repositorio->crear($otroClienteId, $periodo, $idsServicios);
    $assert($facturaOtroCliente > 0, 'Otro cliente sí puede facturar el mismo período.');

    $assert($rechaza(static fn () => $repositorio->crear($clienteId, $periodoSiguiente, [999999])), 'crear rechaza servicios inexistentes.');
    $assert($rechaza(static fn () => $repositorio->crear($clienteId, $periodoPosterior, [])), 'crear exige al menos un servicio.');
    $assert($rechaza(static fn () => $repositorio->crear(999999, $periodoPosterior, $idsServicios)), 'crear rechaza un cliente inexistente.');
    $assert($repositorio->buscarPorId(999999) === null, 'buscarPorId devuelve null cuando la factura no existe.');

    $listado = $repositorio->listar();
    $idsListado = array_column($listado, 'id');
    $assert(in_array($facturaId, $idsListado, true) && in_array($facturaOtroCliente, $idsListado, true), 'listar incluye las facturas creadas.');
    $assert((int) $listado[0]['id'] === $facturaOtroCliente, 'listar ordena por número descendente.');
    $filaNueva = $listado[array_search($facturaId, $idsListado, true)];
    $assert(sprintf('%.2f', (float) $filaNueva['total']) === '74.62', 'listar calcula el total con el cálculo polimórfico del dominio.');
    $assert($filaNueva['cliente'] === $clientes[0]['nombre'], 'listar muestra el nombre del cliente.');

    $masAntigua = $listado[count($listado) - 1];
    $assert(
        abs((float) $masAntigua['total'] - $repositorio->buscarPorId((int) $masAntigua['id'])->calcularTotal()) < 0.0001,
        'El total del listado coincide con el del detalle para la misma factura.'
    );

    $todas = $repositorio->buscarTodas();
    $assert(count($todas) >= 5, 'buscarTodas devuelve las facturas del seed y las nuevas.');
    $delReporte = null;
    foreach ($todas as $entrada) {
        if ((int) $entrada['id'] === $facturaId) {
            $delReporte = $entrada['factura'];
        }
    }
    $assert($delReporte instanceof Factura, 'buscarTodas reconstruye objetos del dominio para el reporte.');
    $assert(abs($delReporte->calcularTotal() - $factura->calcularTotal()) < 0.0001, 'El reporte usa el mismo total que el detalle.');
} finally {
    $pdo->rollBack();
}

fwrite(STDOUT, "OK: repositorio de facturas (crear, detalles, listar, buscar, período único y reporte).\n");
