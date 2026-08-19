<?php

declare(strict_types=1);

namespace App\Contratos;

interface Facturable
{
    public function calcularImporte(): float;

    public function obtenerDescripcion(): string;
}
