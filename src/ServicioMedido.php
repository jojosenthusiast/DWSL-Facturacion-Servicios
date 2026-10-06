<?php

declare(strict_types=1);

namespace App;

use App\Servicios\Servicio;

class ServicioMedido extends Servicio
{
    private float $lecturaAnterior;
    private float $lecturaActual;
    private float $tarifaPorUnidad;

    public function __construct(
        string $id,
        string $nombre,
        float $lecturaAnterior,
        float $lecturaActual,
        float $tarifaPorUnidad
    ) {
        parent::__construct($id, $nombre);

        $this->setLecturas($lecturaAnterior, $lecturaActual);
        $this->setTarifaPorUnidad($tarifaPorUnidad);
    }

    public function setLecturas(float $lecturaAnterior, float $lecturaActual): void
    {
        $this->validarMontoPositivo($lecturaAnterior, 'La lectura anterior');
        $this->validarMontoPositivo($lecturaActual, 'La lectura actual');

        if ($lecturaActual < $lecturaAnterior) {
            throw new \InvalidArgumentException(
                'La lectura actual no puede ser menor que la lectura anterior.'
            );
        }

        $this->lecturaAnterior = $lecturaAnterior;
        $this->lecturaActual = $lecturaActual;
    }

    public function setTarifaPorUnidad(float $tarifaPorUnidad): void
    {
        $this->validarMayorQueCero($tarifaPorUnidad, 'La tarifa por unidad');
        $this->tarifaPorUnidad = $tarifaPorUnidad;
    }

    public function getLecturaAnterior(): float
    {
        return $this->lecturaAnterior;
    }

    public function getLecturaActual(): float
    {
        return $this->lecturaActual;
    }

    public function getTarifaPorUnidad(): float
    {
        return $this->tarifaPorUnidad;
    }

    public function getConsumo(): float
    {
        return $this->lecturaActual - $this->lecturaAnterior;
    }

    public function getTipo(): string
    {
        return 'medido';
    }

    public function tipoLegible(): string
    {
        return 'Medido';
    }

    public function datosEspecificos(): array
    {
        return [
            'lectura_anterior' => $this->getLecturaAnterior(),
            'lectura_actual' => $this->getLecturaActual(),
            'tarifa_por_unidad' => $this->getTarifaPorUnidad(),
        ];
    }

    public function camposEspecificos(): array
    {
        return [
            'lectura_anterior' => ['etiqueta' => 'Lectura anterior', 'tipo' => 'number', 'min' => 0, 'max' => 999999999.999, 'step' => '0.001', 'required' => true],
            'lectura_actual' => ['etiqueta' => 'Lectura actual', 'tipo' => 'number', 'min' => 0, 'max' => 999999999.999, 'step' => '0.001', 'required' => true],
            'tarifa_por_unidad' => ['etiqueta' => 'Tarifa por unidad', 'tipo' => 'number', 'min' => 0.0001, 'max' => 99999999.9999, 'step' => '0.0001', 'required' => true],
        ];
    }

    public function calcularImporte(): float
    {
        return $this->getConsumo() * $this->tarifaPorUnidad;
    }

    public function obtenerDescripcion(): string
    {
        return sprintf(
            '%s (medido) - consumo: %.2f unidades x $%.2f = $%.2f',
            $this->getNombre(),
            $this->getConsumo(),
            $this->tarifaPorUnidad,
            $this->calcularImporte()
        );
    }
}
