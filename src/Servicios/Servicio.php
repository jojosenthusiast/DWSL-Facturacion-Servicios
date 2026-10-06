<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Contratos\Facturable;

abstract class Servicio implements Facturable
{
    private string $id;
    private string $nombre;
    private bool $activo;

    public function __construct(string $id, string $nombre, bool $activo = true)
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException('El id del servicio no puede estar vacio.');
        }

        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre del servicio no puede estar vacio.');
        }

        $this->id = $id;
        $this->nombre = $nombre;
        $this->activo = $activo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function estaActivo(): bool
    {
        return $this->activo;
    }

    public function desactivar(): void
    {
        $this->activo = false;
    }

    protected function validarMontoPositivo(float $monto, string $campo): void
    {
        if (!is_finite($monto) || $monto < 0) {
            throw new \InvalidArgumentException(
                sprintf('%s debe ser un número finito no negativo.', $campo)
            );
        }
    }

    protected function validarMayorQueCero(float $monto, string $campo): void
    {
        if (!is_finite($monto) || $monto <= 0) {
            throw new \InvalidArgumentException(sprintf('%s debe ser mayor que cero.', $campo));
        }
    }
}
