<?php

declare(strict_types=1);

namespace App;

class ServicioTarifaPlana extends Servicio implements Facturable
{
    private float $mensualidad;

    public function __construct(string $id, string $nombre, float $mensualidad)
    {
        parent::__construct($id, $nombre);
        $this->setMensualidad($mensualidad);
    }

    public function setMensualidad(float $mensualidad): void
    {
        $this->validarMontoPositivo($mensualidad, 'La mensualidad');
        $this->mensualidad = $mensualidad;
    }

    public function calcularImporte(): float
    {
        return $this->mensualidad;
    }

    public function obtenerDescripcion(): string
    {
        return sprintf(
            '%s (tarifa plana) - mensualidad: $%.2f',
            $this->getNombre(),
            $this->calcularImporte()
        );
    }
}
