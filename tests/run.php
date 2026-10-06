<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/Web/funciones.php';
require __DIR__ . '/errors.php';

use App\ServicioMedido;
use App\ServicioPorEvento;
use App\ServicioTarifaPlana;
use App\Validation\Validador;

$fallos = [];
$comprobar = static function (bool $condicion, string $caso) use (&$fallos): void {
    if (!$condicion) {
        $fallos[] = $caso;
        fwrite(STDERR, "FALLO: {$caso}\n");
        return;
    }
    fwrite(STDOUT, "OK: {$caso}\n");
};
$lanzaInvalidArgument = static function (callable $accion): bool {
    try { $accion(); } catch (InvalidArgumentException) { return true; }
    return false;
};

$medido = new ServicioMedido('SRV-AGUA', 'Agua', 0.0, 0.0, 1.0);
$comprobar($medido->getConsumo() === 0.0, 'lecturas en cero son válidas');
$comprobar($lanzaInvalidArgument(fn () => $medido->setTarifaPorUnidad(0.0)), 'tarifa medida debe ser positiva');
$comprobar($lanzaInvalidArgument(fn () => new ServicioTarifaPlana('SRV-PLAN', 'Plan', 0.0)), 'mensualidad debe ser positiva');
$comprobar($lanzaInvalidArgument(fn () => new ServicioPorEvento('SRV-EVENTO', 'Evento', 0, 2.0)), 'cantidad de eventos debe ser positiva');
$comprobar($lanzaInvalidArgument(fn () => new ServicioPorEvento('SRV-EVENTO', 'Evento', 1, 0.0)), 'tarifa por evento debe ser positiva');

$validador = (new Validador())
    ->requerido('nombre', '   ')
    ->email('correo', 'invalido')
    ->longitudMaxima('texto', 'café', 3)
    ->numeroPositivo('tarifa', 'INF')
    ->enteroPositivo('cantidad', '2.5')
    ->rango('edad', 150, 0, 120);
$comprobar(!$validador->esValido() && count($validador->errores()) === 6, 'validador acumula errores sin coerción ni infinitos');
$comprobar((new Validador())->email('correo', 'ana@example.test')->numeroPositivo('tarifa', '1.25')->enteroPositivo('cantidad', '2')->esValido(), 'validador acepta datos válidos');
$comprobar(e('<script>') === '&lt;script&gt;', 'e escapa salida HTML');
$_SESSION = [];
$token = csrf_token();
$comprobar(strlen($token) === 64 && csrf_verificar($token), 'CSRF genera y verifica token');
$comprobar(!csrf_verificar('incorrecto'), 'CSRF rechaza token incorrecto');
flash('success', 'Guardado');
$comprobar(flash_obtener('success') === 'Guardado' && flash_obtener('success') === null, 'flash se consume una sola vez');

if ($fallos !== []) {
    exit(1);
}
