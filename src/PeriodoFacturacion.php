<?php

declare(strict_types=1);

namespace App;

final class PeriodoFacturacion
{
    private \DateTimeImmutable $inicio;
    private \DateTimeImmutable $fin;

    public function __construct(\DateTimeImmutable $inicio, \DateTimeImmutable $fin)
    {
        if ($fin <= $inicio) {
            throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio.');
        }

        $this->inicio = $inicio;
        $this->fin = $fin;
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
