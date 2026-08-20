<?php

declare(strict_types=1);

namespace App\Reportes;

use App\Factura;

final class ReporteFacturaConsola
{
    public function generar(Factura $factura): string
    {
        $lineas = [];

        $lineas[] = str_repeat('=', 60);
        $lineas[] = 'FACTURA MENSUAL';
        $lineas[] = str_repeat('=', 60);

        $lineas[] = sprintf(
            'Cliente: %s',
            $factura->obtenerCliente()->getNombre()
        );

        $lineas[] = sprintf(
            'Periodo: %s',
            $factura->obtenerPeriodo()->obtenerEtiqueta()
        );

        $lineas[] = str_repeat('-', 60);

        foreach ($factura->obtenerLineas() as $indice => $linea) {
            $lineas[] = sprintf(
                'Servicio %d',
                $indice + 1
            );

            $lineas[] = sprintf(
                'Descripcion: %s',
                $linea['descripcion']
            );

            $lineas[] = sprintf(
                'Importe: $%.2f',
                $linea['importe']
            );

            $lineas[] = '';
        }

        $lineas[] = str_repeat('-', 60);

        $lineas[] = sprintf(
            'Total: $%.2f',
            $factura->calcularTotal()
        );

        $lineas[] = str_repeat('=', 60);

        return implode(PHP_EOL, $lineas);
    }
}