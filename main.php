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

$cliente = new Cliente('CLI-001', 'Ana Martinez', 'ana.martinez@example.com');

$periodo = new PeriodoFacturacion(
    new DateTimeImmutable('2026-07-01'),
    new DateTimeImmutable('2026-07-31')
);

$factura = new Factura($cliente, $periodo);

$factura->agregarServicio(new ServicioMedido('SRV-AGUA', 'Agua potable', 120.0, 145.5, 0.85));
$factura->agregarServicio(new ServicioMedido('SRV-ENER', 'Energia electrica', 980.0, 1050.0, 0.22));
$factura->agregarServicio(new ServicioTarifaPlana('SRV-INTERNET', 'Internet residencial', 35.0));
$factura->agregarServicio(new ServicioPorEvento('SRV-MANT', 'Mantenimiento de areas comunes', 2, 15.0));

$reporte = new ReporteFacturaConsola();

echo $reporte->generar($factura) . PHP_EOL;
