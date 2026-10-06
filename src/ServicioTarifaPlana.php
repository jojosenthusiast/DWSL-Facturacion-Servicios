<?php

declare(strict_types=1);

namespace App;

use App\Servicios\Servicio;

class ServicioTarifaPlana extends Servicio
{
    private float $mensualidad;

    public function __construct(string $id, string $nombre, float $mensualidad)
    {
        parent::__construct($id, $nombre);
        $this->setMensualidad($mensualidad);
    }

    public function setMensualidad(float $mensualidad): void
    {
        $this->validarMayorQueCero($mensualidad, 'La mensualidad');
        $this->mensualidad = $mensualidad;
    }

    public function getMensualidad(): float
    {
        return $this->mensualidad;
    }

    public function getTipo(): string
    {
        return 'tarifa_plana';
    }

    public function tipoLegible(): string
    {
        return 'Tarifa plana';
    }

    public function datosEspecificos(): array
    {
        return ['mensualidad' => $this->getMensualidad()];
    }

    public function camposEspecificos(): array
    {
        return [
            'mensualidad' => ['etiqueta' => 'Mensualidad', 'tipo' => 'number', 'min' => 0.01, 'max' => 9999999999.99, 'step' => '0.01', 'required' => true],
        ];
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
