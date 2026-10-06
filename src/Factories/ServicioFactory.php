<?php

declare(strict_types=1);

namespace App\Factories;

use App\ServicioMedido;
use App\ServicioPorEvento;
use App\ServicioTarifaPlana;
use App\Servicios\Servicio;
use App\Validation\Validador;
use InvalidArgumentException;

/** [FABRICA] Único lugar de Fase 2 que selecciona las clases concretas. */
final class ServicioFactory
{
    public static function desdeFila(array $fila): Servicio
    {
        $servicio = self::construir($fila, true);
        $servicio->setIdBd(self::entero($fila, 'id'));

        return $servicio;
    }

    /** El ID se obtiene de la ruta controlada, nunca de un campo oculto del formulario. */
    public static function desdeFormulario(array $datos): Servicio
    {
        return self::construir($datos, false);
    }

    /** @return array<string, string> Opciones para la selección del formulario. */
    public static function tiposDisponibles(): array
    {
        $tipos = [];
        foreach (self::plantillas() as $tipo => $servicio) {
            $tipos[$tipo] = $servicio->tipoLegible();
        }

        return $tipos;
    }

    /** Metadatos del modelo para crear formularios sin condicionales por tipo. */
    public static function camposPorTipo(): array
    {
        return array_map(static fn (Servicio $servicio): array => $servicio->camposEspecificos(), self::plantillas());
    }

    private static function construir(array $datos, bool $activoPorDefecto): Servicio
    {
        $codigo = self::texto($datos, 'codigo', 30);
        $nombre = self::texto($datos, 'nombre', 120);
        $tipo = self::texto($datos, 'tipo', 20);

        $servicio = match ($tipo) {
            'medido' => new ServicioMedido(
                $codigo, $nombre,
                self::numero($datos, 'lectura_anterior'),
                self::numero($datos, 'lectura_actual'),
                self::numero($datos, 'tarifa_por_unidad')
            ),
            'tarifa_plana' => new ServicioTarifaPlana($codigo, $nombre, self::numero($datos, 'mensualidad')),
            // La consigna usa evento; schema.sql y seed.sql de Carlos usan por_evento.
            'evento', 'por_evento' => new ServicioPorEvento(
                $codigo, $nombre,
                self::entero($datos, 'cantidad_eventos'),
                self::numero($datos, 'tarifa_por_evento')
            ),
            default => throw new InvalidArgumentException('El tipo de servicio no es válido.'),
        };

        // [VALIDACION] Los límites web se obtienen de la misma API que usan las vistas.
        $validador = new Validador();
        foreach ($servicio->camposEspecificos() as $campo => $reglas) {
            $validador->rango($campo, $datos[$campo], $reglas['min'], $reglas['max']);
        }
        if (!$validador->esValido()) {
            throw new InvalidArgumentException(self::mensajeErrores($validador));
        }

        $activo = $datos['activo'] ?? $activoPorDefecto;
        if (!in_array($activo, [true, false, 0, 1, '0', '1', 'on'], true)) {
            throw new InvalidArgumentException('El estado activo no es válido.');
        }
        $servicio->setActivo(in_array($activo, [true, 1, '1', 'on'], true));

        $imagen = $datos['imagen'] ?? null;
        if ($imagen !== null && !is_string($imagen)) {
            throw new InvalidArgumentException('El nombre de la imagen no es válido.');
        }
        $servicio->setImagen($imagen === '' ? null : $imagen);

        return $servicio;
    }

    /** Los objetos de referencia solo proporcionan etiquetas y metadatos de campos. */
    private static function plantillas(): array
    {
        return [
            'medido' => new ServicioMedido('PLANTILLA', 'Servicio', 0, 0, 1),
            'tarifa_plana' => new ServicioTarifaPlana('PLANTILLA', 'Servicio', 1),
            'por_evento' => new ServicioPorEvento('PLANTILLA', 'Servicio', 1, 1),
        ];
    }

    private static function texto(array $datos, string $campo, int $maximo): string
    {
        $valor = $datos[$campo] ?? null;
        $validador = (new Validador())->requerido($campo, $valor)->longitudMaxima($campo, $valor, $maximo);
        if (!$validador->esValido()) {
            throw new InvalidArgumentException(self::mensajeErrores($validador));
        }

        return trim($valor);
    }

    private static function numero(array $datos, string $campo): float
    {
        $valor = $datos[$campo] ?? null;
        if ((!is_int($valor) && !is_float($valor) && !is_string($valor))
            || !is_numeric($valor) || !is_finite((float) $valor)) {
            throw new InvalidArgumentException("El campo {$campo} debe ser un número finito.");
        }

        return (float) $valor;
    }

    private static function entero(array $datos, string $campo): int
    {
        $valor = $datos[$campo] ?? null;
        $validador = (new Validador())->enteroPositivo($campo, $valor);
        if (!$validador->esValido()) {
            throw new InvalidArgumentException(self::mensajeErrores($validador));
        }

        return (int) $valor;
    }

    private static function mensajeErrores(Validador $validador): string
    {
        $mensajes = [];
        foreach ($validador->errores() as $campo => $errores) {
            $mensajes[] = $campo . ': ' . implode(' ', $errores);
        }

        return implode(' ', $mensajes);
    }
}
