<?php

declare(strict_types=1);

namespace App;

use App\Contratos\Facturable;
use App\Servicios\Servicio;

class ServicioPorEvento extends Servicio implements Facturable
{
    private int $cantidadEventos;
    private float $tarifaPorEvento;

    public function __construct(string $id, string $nombre, int $cantidadEventos, float $tarifaPorEvento)
    {
        parent::__construct($id, $nombre);
        $this->setCantidadEventos($cantidadEventos);
        $this->setTarifaPorEvento($tarifaPorEvento);
    }

    public function setCantidadEventos(int $cantidadEventos): void
    {
        if ($cantidadEventos < 0) {
            throw new \InvalidArgumentException('La cantidad de eventos no puede ser negativa.');
        }

        $this->cantidadEventos = $cantidadEventos;
    }

    public function setTarifaPorEvento(float $tarifaPorEvento): void
    {
        $this->validarMontoPositivo($tarifaPorEvento, 'La tarifa por evento');
        $this->tarifaPorEvento = $tarifaPorEvento;
    }

    public function calcularImporte(): float
    {
        return $this->cantidadEventos * $this->tarifaPorEvento;
    }

    public function obtenerDescripcion(): string
    {
        return sprintf(
            '%s (por evento) - %d eventos x $%.2f = $%.2f',
            $this->getNombre(),
            $this->cantidadEventos,
            $this->tarifaPorEvento,
            $this->calcularImporte()
        );
    }
}
