<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require dirname(__DIR__, 2) . '/src/Web/formulario.php';

use App\PeriodoFacturacion;
use App\Repositories\FacturaRepositorio;
use App\Validation\Validador;

$formularioUrl = '/facturas/crear.php';

$volverAlFormulario = static function (array $valores, array $errores, ?string $mensaje = null) use ($formularioUrl): never {
    formulario_guardar($valores, $errores);
    if ($mensaje !== null) {
        flash('error', $mensaje);
    }
    header('Location: ' . $formularioUrl, true, 303);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $formularioUrl, true, 303);
    exit;
}

csrf_exigir_post();

$clienteRecibido = $_POST['cliente_id'] ?? '';
$periodoRecibido = $_POST['periodo'] ?? '';
$serviciosRecibidos = $_POST['servicios'] ?? [];

$valores = [
    'cliente_id' => is_scalar($clienteRecibido) ? (string) $clienteRecibido : '',
    'periodo' => is_scalar($periodoRecibido) ? (string) $periodoRecibido : '',
    'servicios' => is_array($serviciosRecibidos)
        ? array_values(array_filter(
            array_map(static fn (mixed $id): string => is_scalar($id) ? (string) $id : '', $serviciosRecibidos),
            static fn (string $id): bool => $id !== ''
        ))
        : [],
];

$validador = (new Validador())
    ->requerido('cliente_id', $valores['cliente_id'], 'Selecciona un cliente.')
    ->enteroPositivo('cliente_id', $valores['cliente_id'], 'El cliente seleccionado no es válido.')
    ->requerido('periodo', $valores['periodo'], 'Selecciona el período a facturar.');

$errores = $validador->errores();

$periodo = null;
if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $valores['periodo']) === 1) {
    $inicio = DateTimeImmutable::createFromFormat('Y-m-d', $valores['periodo'] . '-01');
    if ($inicio instanceof DateTimeImmutable) {
        $periodo = new PeriodoFacturacion($inicio, $inicio->modify('last day of this month'));
    }
}
if ($valores['periodo'] !== '' && $periodo === null) {
    $errores['periodo'][] = 'El período debe ser un mes válido con formato AAAA-MM.';
}

if ($valores['servicios'] === []) {
    $errores['servicios'][] = 'Selecciona al menos un servicio.';
} elseif (array_filter($valores['servicios'], static fn (string $id): bool => preg_match('/^[0-9]+$/D', $id) !== 1) !== []) {
    $errores['servicios'][] = 'Los servicios seleccionados no son válidos.';
}

$repositorio = new FacturaRepositorio(conexion_obtener());

if ($errores === [] && $periodo instanceof PeriodoFacturacion
    && $repositorio->existePeriodo((int) $valores['cliente_id'], $periodo->getInicio(), $periodo->getFin())) {
    $errores['periodo'][] = 'Ya existe una factura para ese cliente en ese período.';
}

if ($errores !== [] || !$periodo instanceof PeriodoFacturacion) {
    $volverAlFormulario($valores, $errores, 'Revisa los campos marcados para poder crear la factura.');
}

try {
    $facturaId = $repositorio->crear(
        (int) $valores['cliente_id'],
        $periodo,
        array_map('intval', $valores['servicios'])
    );
} catch (InvalidArgumentException $exception) {
    $volverAlFormulario($valores, ['general' => [$exception->getMessage()]], $exception->getMessage());
}

flash('success', sprintf('La factura #%d se creó correctamente.', $facturaId));

header('Location: /facturas/ver.php?id=' . $facturaId, true, 303);
exit;
