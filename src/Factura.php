<?php

declare(strict_types=1);

namespace App;

use App\Contratos\Facturable;

final class Factura
{
    private Cliente $cliente;
    private PeriodoFacturacion $periodo;

    /** @var Facturable[] */
    private array $facturables = [];

    public function __construct(Cliente $cliente, PeriodoFacturacion $periodo)
    {
        $this->cliente = $cliente;
        $this->periodo = $periodo;
    }

    public function obtenerCliente(): Cliente
    {
        return $this->cliente;
    }

    public function obtenerPeriodo(): PeriodoFacturacion
    {
        return $this->periodo;
    }

    public function agregarFacturable(Facturable $facturable): void
    {
        $this->facturables[] = $facturable;
    }

    /**
     * @return Facturable[]
     */
    public function obtenerFacturables(): array
    {
        return $this->facturables;
    }

    public function calcularTotal(): float
    {
        $total = 0.0;

        foreach ($this->facturables as $facturable) {
            $total += $facturable->calcularImporte();
        }

        return $total;
    }

    /**
     * @return list<array{descripcion: string, importe: float}>
     */
    public function obtenerLineas(): array
    {
        return array_map(
            static fn (Facturable $facturable): array => [
                'descripcion' => $facturable->obtenerDescripcion(),
                'importe' => $facturable->calcularImporte(),
            ],
            $this->facturables
        );
    }

    /**
     * @return string[]
     */
    public function generarDetalle(): array
    {
        return array_map(
            static fn (array $linea): string => $linea['descripcion'],
            $this->obtenerLineas()
        );
    }
}
