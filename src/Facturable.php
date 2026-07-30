<?php

declare(strict_types=1);

namespace App;

interface Facturable
{
    public function calcularImporte(): float;

    public function obtenerDescripcion(): string;
}
