<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\ServicioMedido;
use App\ServicioPorEvento;
use App\ServicioTarifaPlana;

$assert = static function (bool $condition, string $case): void {
    if (!$condition) { throw new RuntimeException("Failed: {$case}"); }
    echo "OK: {$case}\n";
};
$rejects = static function (callable $action): bool {
    try { $action(); } catch (InvalidArgumentException) { return true; }
    return false;
};
$measured = new ServicioMedido('SRV-AGUA', 'Agua', 0.0, 0.0, 1.0);
$assert($measured->getConsumo() === 0.0, 'zero readings remain valid');
$assert($rejects(fn () => new ServicioMedido('SRV-NAN', 'Lectura', NAN, NAN, 1.0)), 'non-finite readings are rejected');
$assert($rejects(fn () => $measured->setTarifaPorUnidad(0.0)), 'measured rate must be positive');
$assert($rejects(fn () => new ServicioTarifaPlana('SRV-PLAN', 'Plan', 0.0)), 'flat rate must be positive');
$assert($rejects(fn () => new ServicioPorEvento('SRV-EVENTO', 'Evento', 0, 2.0)), 'event count must be positive');
$assert($rejects(fn () => new ServicioPorEvento('SRV-EVENTO', 'Evento', 1, 0.0)), 'event rate must be positive');
