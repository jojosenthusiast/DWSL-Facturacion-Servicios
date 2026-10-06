<?php

declare(strict_types=1);

namespace App;

use App\Servicios\Servicio;

class ServicioPorEvento extends Servicio
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
        if ($cantidadEventos <= 0) {
            throw new \InvalidArgumentException('La cantidad de eventos debe ser mayor que cero.');
        }

        $this->cantidadEventos = $cantidadEventos;
    }

    public function setTarifaPorEvento(float $tarifaPorEvento): void
    {
        $this->validarMayorQueCero($tarifaPorEvento, 'La tarifa por evento');
        $this->tarifaPorEvento = $tarifaPorEvento;
    }

    public function getCantidadEventos(): int
    {
        return $this->cantidadEventos;
    }

    public function getTarifaPorEvento(): float
    {
        return $this->tarifaPorEvento;
    }

    public function getTipo(): string
    {
        return 'por_evento';
    }

    public function tipoLegible(): string
    {
        return 'Por evento';
    }

    public function datosEspecificos(): array
    {
        return [
            'cantidad_eventos' => $this->getCantidadEventos(),
            'tarifa_por_evento' => $this->getTarifaPorEvento(),
        ];
    }

    public function camposEspecificos(): array
    {
        return [
            'cantidad_eventos' => ['etiqueta' => 'Cantidad de eventos', 'tipo' => 'number', 'min' => 1, 'max' => 4294967295, 'step' => '1', 'required' => true],
            'tarifa_por_evento' => ['etiqueta' => 'Tarifa por evento', 'tipo' => 'number', 'min' => 0.01, 'max' => 9999999999.99, 'step' => '0.01', 'required' => true],
        ];
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
