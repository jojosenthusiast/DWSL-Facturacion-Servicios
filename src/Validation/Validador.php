<?php

declare(strict_types=1);

namespace App\Validation;

/** [VALIDACION] Reusable, accumulating validation rules for web forms. */
final class Validador
{
    /** @var array<string, list<string>> */
    private array $errores = [];

    public function requerido(string $campo, mixed $valor, string $mensaje = 'Este campo es obligatorio.'): self
    {
        if (!is_scalar($valor) || trim((string) $valor) === '') {
            $this->agregarError($campo, $mensaje);
        }

        return $this;
    }

    public function email(string $campo, mixed $valor, string $mensaje = 'Ingresa un correo electrónico válido.'): self
    {
        if (!is_string($valor) || filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
            $this->agregarError($campo, $mensaje);
        }

        return $this;
    }

    public function longitudMaxima(string $campo, mixed $valor, int $maximo, string $mensaje = ''): self
    {
        if ($maximo < 0) {
            throw new \InvalidArgumentException('La longitud máxima no puede ser negativa.');
        }

        $longitud = is_string($valor) ? preg_match_all('/./us', $valor, $matches) : false;
        if (!is_string($valor) || $longitud === false || $longitud > $maximo) {
            $this->agregarError($campo, $mensaje !== '' ? $mensaje : "No debe exceder {$maximo} caracteres.");
        }

        return $this;
    }

    public function numeroPositivo(string $campo, mixed $valor, string $mensaje = 'Debe ser un número mayor que cero.'): self
    {
        if (!$this->esNumeroFinito($valor) || (float) $valor <= 0) {
            $this->agregarError($campo, $mensaje);
        }

        return $this;
    }

    public function enteroPositivo(string $campo, mixed $valor, string $mensaje = 'Debe ser un entero mayor que cero.'): self
    {
        if ((!is_int($valor) && (!is_string($valor) || !preg_match('/^[0-9]+$/D', $valor)))
            || filter_var($valor, FILTER_VALIDATE_INT) === false
            || (int) $valor <= 0) {
            $this->agregarError($campo, $mensaje);
        }

        return $this;
    }

    public function rango(string $campo, mixed $valor, float $minimo, float $maximo, string $mensaje = ''): self
    {
        if ($minimo > $maximo) {
            throw new \InvalidArgumentException('El mínimo no puede superar el máximo.');
        }

        if (!$this->esNumeroFinito($valor) || (float) $valor < $minimo || (float) $valor > $maximo) {
            $this->agregarError($campo, $mensaje !== '' ? $mensaje : "Debe estar entre {$minimo} y {$maximo}.");
        }

        return $this;
    }

    public function esValido(): bool
    {
        return $this->errores === [];
    }

    /** @return array<string, list<string>> */
    public function errores(): array
    {
        return $this->errores;
    }

    private function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo][] = $mensaje;
    }

    private function esNumeroFinito(mixed $valor): bool
    {
        if ((!is_int($valor) && !is_float($valor) && !is_string($valor)) || trim((string) $valor) === '') {
            return false;
        }

        return is_numeric($valor) && is_finite((float) $valor);
    }
}
