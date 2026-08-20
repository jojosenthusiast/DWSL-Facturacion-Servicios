<?php

declare(strict_types=1);

namespace App;

final class PeriodoFacturacion
{
    public function __construct(
        private readonly \DateTimeImmutable $inicio,
        private readonly \DateTimeImmutable $fin
    )
    {
        if ($fin <= $inicio) {
            throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio.');
        }
    }

    public function getInicio(): \DateTimeImmutable
    {
        return $this->inicio;
    }

    public function getFin(): \DateTimeImmutable
    {
        return $this->fin;
    }

    public function obtenerEtiqueta(): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        $mes = (int) $this->inicio->format('n');
        $anio = $this->inicio->format('Y');

        return sprintf('%s %s', $meses[$mes], $anio);
    }
}
