<?php

declare(strict_types=1);

namespace App;

use App\Contratos\Facturable;

final class Factura
{
    private Cliente $cliente;
    private PeriodoFacturacion $periodo;

    /** @var Facturable[] */
    private array $servicios = [];

    public function __construct(Cliente $cliente, PeriodoFacturacion $periodo)
    {
        $this->cliente = $cliente;
        $this->periodo = $periodo;
    }

    public function agregarServicio(Facturable $servicio): void
    {
        $this->servicios[] = $servicio;
    }

    public function calcularTotal(): float
    {
        $total = 0.0;

        foreach ($this->servicios as $servicio) {
            $total += $servicio->calcularImporte();
        }

        return $total;
    }

    public function generarDetalle(): array
    {
        return array_map(
            static fn (Facturable $servicio): string => $servicio->obtenerDescripcion(),
            $this->servicios
        );
    }

    public function imprimir(): string
    {
        $lineas = [];
        $lineas[] = str_repeat('=', 60);
        $lineas[] = sprintf('FACTURA - %s', $this->periodo->obtenerEtiqueta());
        $lineas[] = str_repeat('=', 60);
        $lineas[] = sprintf('Cliente: %s (%s)', $this->cliente->getNombre(), $this->cliente->getCorreo());
        $lineas[] = str_repeat('-', 60);

        foreach ($this->generarDetalle() as $detalle) {
            $lineas[] = $detalle;
        }

        $lineas[] = str_repeat('-', 60);
        $lineas[] = sprintf('TOTAL A PAGAR: $%.2f', $this->calcularTotal());
        $lineas[] = str_repeat('=', 60);

        return implode(PHP_EOL, $lineas);
    }
}
