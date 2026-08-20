<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Cliente;
use App\Factura;
use App\PeriodoFacturacion;
use App\ServicioMedido;
use App\ServicioPorEvento;
use App\ServicioTarifaPlana;
use App\Reportes\ReporteFacturaConsola;
use App\Infrastructure\Export\ExportadorFacturaJson; 

$cliente = new Cliente('CLI-001', 'Ana Martinez', 'ana.martinez@example.com');

$periodo = new PeriodoFacturacion(
    new DateTimeImmutable('2026-07-01'),
    new DateTimeImmutable('2026-07-31')
);

$factura = new Factura($cliente, $periodo);

$factura->agregarFacturable(new ServicioMedido('SRV-AGUA', 'Agua potable', 120.0, 145.5, 0.85));
$factura->agregarFacturable(new ServicioMedido('SRV-ENER', 'Energia electrica', 980.0, 1050.0, 0.22));
$factura->agregarFacturable(new ServicioTarifaPlana('SRV-INTERNET', 'Internet residencial', 35.0));
$factura->agregarFacturable(new ServicioPorEvento('SRV-MANT', 'Mantenimiento de areas comunes', 2, 15.0));

$reporte = new ReporteFacturaConsola();

echo $reporte->generar($factura) . PHP_EOL;

echo PHP_EOL . "Exportando factura a JSON..." . PHP_EOL;

$exportador = new ExportadorFacturaJson();
$exportacionExitosa = $exportador->exportar($factura);

if ($exportacionExitosa) {
    echo "Factura exportada exitosamente a JSON." . PHP_EOL;
    echo "Archivo generado: storage/factura_demo.json" . PHP_EOL;
} else {
    echo "Error al exportar la factura a JSON. Revisa los logs." . PHP_EOL;
}

echo PHP_EOL . "--- Contenido del JSON generado ---" . PHP_EOL;
$archivoJson = 'storage/factura_demo.json';
if (file_exists($archivoJson)) {
    echo file_get_contents($archivoJson) . PHP_EOL;
} else {
    echo "El archivo JSON no se generó correctamente." . PHP_EOL;
}