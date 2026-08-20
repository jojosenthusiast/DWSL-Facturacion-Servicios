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
        $this->validarMontoPositivo($tarifaPorUnidad, 'La tarifa por unidad');
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
