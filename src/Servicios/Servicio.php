<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Contratos\Facturable;

abstract class Servicio implements Facturable
{
    // El id de Fase 1 sigue siendo el código; la PK de BD se guarda aparte.
    private ?int $idBd = null;
    private ?string $imagen = null;

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

    public function getCodigo(): string
    {
        return $this->getId();
    }

    public function getIdBd(): ?int
    {
        return $this->idBd;
    }

    public function setIdBd(int $idBd): void
    {
        if ($idBd <= 0) {
            throw new \InvalidArgumentException('El ID de BD debe ser mayor que cero.');
        }

        $this->idBd = $idBd;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    /** [SEGURIDAD] Solo se almacena el nombre entregado por GestorImagenes. */
    public function setImagen(?string $imagen): void
    {
        if ($imagen !== null && ($imagen === '' || strlen($imagen) > 255
            || str_contains($imagen, '/') || str_contains($imagen, '\\')
            || str_contains($imagen, "\0") || $imagen === '.' || $imagen === '..')) {
            throw new \InvalidArgumentException('El nombre de la imagen no es válido.');
        }

        $this->imagen = $imagen;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    abstract public function getTipo(): string;

    /** [POLIMORFISMO] Las vistas usan esta API sin identificar subclases. */
    abstract public function tipoLegible(): string;

    /** @return array<string, int|float> Valores con los mismos nombres que la BD. */
    abstract public function datosEspecificos(): array;

    /**
     * @return array<string, array{etiqueta:string, tipo:string, min:int|float, max:int|float, step:string, required:bool}>
     */
    abstract public function camposEspecificos(): array;

    /** @return array<string, int|float|string|null> Datos para Single Table Inheritance. */
    public function datosPersistibles(): array
    {
        // [VALIDACION] También protege objetos creados directamente en Fase 1.
        foreach (['codigo' => [$this->getCodigo(), 30], 'nombre' => [$this->getNombre(), 120]] as $campo => [$valor, $maximo]) {
            $longitud = preg_match_all('/./us', $valor);
            if ($longitud === false || $longitud > $maximo) {
                throw new \InvalidArgumentException("El campo {$campo} no debe exceder {$maximo} caracteres.");
            }
        }

        return array_replace([
            'codigo' => $this->getCodigo(),
            'nombre' => $this->getNombre(),
            'tipo' => $this->getTipo(),
            'activo' => $this->estaActivo() ? 1 : 0,
            'imagen' => $this->getImagen(),
            'lectura_anterior' => null,
            'lectura_actual' => null,
            'tarifa_por_unidad' => null,
            'mensualidad' => null,
            'cantidad_eventos' => null,
            'tarifa_por_evento' => null,
        ], $this->datosEspecificos());
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
