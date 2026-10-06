<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Factories\ServicioFactory;
use App\Servicios\Servicio;

$comprobar = static function (bool $condicion, string $caso): void {
    if (!$condicion) {
        throw new RuntimeException($caso);
    }
    echo "OK: {$caso}\n";
};
$rechaza = static function (callable $accion): bool {
    try { $accion(); } catch (InvalidArgumentException) { return true; }
    return false;
};

$base = ['codigo' => 'PRUEBA', 'nombre' => 'Servicio de prueba', 'activo' => '1', 'imagen' => 'prueba.webp'];
$casos = [
    ['datos' => ['tipo' => 'medido', 'lectura_anterior' => '0', 'lectura_actual' => '12.5', 'tarifa_por_unidad' => '1.25'], 'clase' => 'App\\ServicioMedido', 'importe' => 15.625],
    ['datos' => ['tipo' => 'tarifa_plana', 'mensualidad' => '35.00'], 'clase' => 'App\\ServicioTarifaPlana', 'importe' => 35.0],
    ['datos' => ['tipo' => 'evento', 'cantidad_eventos' => '2', 'tarifa_por_evento' => '12.00'], 'clase' => 'App\\ServicioPorEvento', 'importe' => 24.0],
];

foreach ($casos as $caso) {
    $servicio = ServicioFactory::desdeFormulario($caso['datos'] + $base + ['id' => '99']);
    $comprobar(get_class($servicio) === $caso['clase'], 'Factory selecciona ' . $caso['clase']);
    $comprobar($servicio->getId() === 'PRUEBA' && $servicio->getIdBd() === null, 'mantiene código de Fase 1 e ignora ID del formulario');
    $comprobar(abs($servicio->calcularImporte() - $caso['importe']) < 0.000001, 'mantiene cálculo del dominio');

    $fila = $servicio->datosPersistibles() + ['id' => '7'];
    foreach ($fila as $campo => $valor) {
        if (is_int($valor) || is_float($valor)) {
            $fila[$campo] = (string) $valor; // PDO puede devolver números como texto.
        }
    }
    $reconstruido = ServicioFactory::desdeFila($fila);
    $comprobar(get_class($reconstruido) === $caso['clase'] && $reconstruido->getIdBd() === 7
        && $reconstruido->datosPersistibles() === $servicio->datosPersistibles(), 'reconstruye todos los datos desde una fila PDO');

    // Uso de vista: solo se conoce Servicio y se recorre su API polimórfica.
    $presentar = static function (Servicio $modelo): string {
        $salida = $modelo->tipoLegible() . ': ' . $modelo->obtenerDescripcion();
        foreach ($modelo->datosEspecificos() as $campo => $valor) {
            $salida .= ' | ' . $modelo->camposEspecificos()[$campo]['etiqueta'] . ': ' . $valor;
        }
        return $salida;
    };
    $comprobar($presentar($reconstruido) !== '', 'vista genérica consume valores y etiquetas sin comprobar subclases');
}

$evento = ServicioFactory::desdeFormulario(['tipo' => 'por_evento', 'cantidad_eventos' => '1', 'tarifa_por_evento' => '5'] + $base);
$comprobar($evento->getTipo() === 'por_evento', 'acepta evento y por_evento y respeta tipo del esquema de Carlos');
$comprobar(count(ServicioFactory::tiposDisponibles()) === 3 && count(ServicioFactory::camposPorTipo()) === 3, 'Factory entrega selección y campos de los tres tipos');
$sinCheckbox = ServicioFactory::desdeFormulario(['tipo' => 'tarifa_plana', 'mensualidad' => '5', 'codigo' => 'NUEVO', 'nombre' => 'Nuevo']);
$comprobar(!$sinCheckbox->estaActivo(), 'checkbox ausente conserva el estado desmarcado');
$inactivo = ServicioFactory::desdeFila(['id' => '8', 'tipo' => 'tarifa_plana', 'mensualidad' => '5', 'activo' => '0'] + $base);
$comprobar(!$inactivo->estaActivo() && $inactivo->getImagen() === 'prueba.webp', 'conserva estado inactivo e imagen al reconstruir');

$medido = $casos[0]['datos'] + $base;
$eventoDatos = $casos[2]['datos'] + $base;
foreach ([
    array_replace($medido, ['tipo' => 'desconocido']),
    array_replace($medido, ['lectura_actual' => '-1']),
    array_replace($medido, ['lectura_anterior' => '13']),
    array_replace($medido, ['tarifa_por_unidad' => '0']),
    array_replace($medido, ['tarifa_por_unidad' => '0.00001']),
    array_replace($medido, ['lectura_actual' => INF]),
    array_replace($medido, ['lectura_actual' => ['1']]),
    array_replace($medido, ['lectura_actual' => 'invalido']),
    array_replace($medido, ['nombre' => str_repeat('ñ', 121)]),
    array_replace($medido, ['codigo' => '']),
    array_replace($medido, ['imagen' => '../privado.php']),
    array_replace($medido, ['activo' => 'invalido']),
    array_replace($eventoDatos, ['cantidad_eventos' => '2.5']),
    array_replace($eventoDatos, ['cantidad_eventos' => '0']),
    array_replace($eventoDatos, ['tarifa_por_evento' => '0']),
    ['tipo' => 'tarifa_plana', 'mensualidad' => '0'] + $base,
] as $datosInvalidos) {
    $comprobar($rechaza(static fn () => ServicioFactory::desdeFormulario($datosInvalidos)), 'rechaza entrada inválida sin convertirla silenciosamente');
}
$comprobar($rechaza(static fn () => ServicioFactory::desdeFila($medido + ['id' => '0'])), 'rechaza PK inválida en una fila');
