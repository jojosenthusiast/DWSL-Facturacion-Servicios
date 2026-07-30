<?php

declare(strict_types=1);

namespace App;

abstract class Servicio
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
        if ($monto < 0) {
            throw new \InvalidArgumentException(
                sprintf('%s no puede ser negativo (valor recibido: %.2f).', $campo, $monto)
            );
        }
    }
}
