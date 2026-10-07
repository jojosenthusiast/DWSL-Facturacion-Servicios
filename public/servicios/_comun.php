<?php

declare(strict_types=1);

use App\Factories\ServicioFactory;
use App\Services\GestorImagenes;
use App\Servicios\Servicio;
use App\Validation\Validador;

/** [PRG] Redirección 303 tras operaciones POST. */
function servicios_redirigir(string $destino): never
{
    header('Location: ' . $destino, true, 303);
    exit;
}

/** [PRG] Conserva valores y errores únicamente hasta la siguiente petición GET. */
function servicios_formulario_guardar(string $clave, array $valores, array $errores): void
{
    sesion_iniciar();
    $_SESSION['_servicios_form'][$clave] = [
        'valores' => $valores,
        'errores' => $errores,
    ];
}

/** @return array{valores:array, errores:array}|null */
function servicios_formulario_extraer(string $clave): ?array
{
    sesion_iniciar();
    $estado = $_SESSION['_servicios_form'][$clave] ?? null;
    unset($_SESSION['_servicios_form'][$clave]);

    return is_array($estado) ? $estado : null;
}

/** [SEGURIDAD] Solo acepta identificadores positivos provenientes de la ruta. */
function servicios_id_desde_peticion(mixed $valor): ?int
{
    if (!is_string($valor) && !is_int($valor)) {
        return null;
    }

    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : (int) $id;
}

function servicios_404(): never
{
    http_response_code(404);
    exit('Servicio no encontrado.');
}

function servicios_405(): never
{
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

/** [SEGURIDAD] URL pública derivada exclusivamente del nombre saneado por GestorImagenes. */
function servicios_imagen_url(GestorImagenes $gestor, ?string $nombre): string
{
    return '/uploads/' . rawurlencode($gestor->obtenerImagen($nombre));
}

/** @return array<string, mixed> [POLIMORFISMO] Extrae datos sin reconocer subclases. */
function servicios_valores_modelo(Servicio $servicio): array
{
    return array_replace([
        'codigo' => $servicio->getCodigo(),
        'nombre' => $servicio->getNombre(),
        'tipo' => $servicio->getTipo(),
        'activo' => $servicio->estaActivo() ? '1' : '0',
    ], $servicio->datosEspecificos());
}

/**
 * [VALIDACION] Valida campos comunes y metadatos específicos publicados por los modelos.
 * @return array<string, list<string>>
 */
function servicios_validar_formulario(array $datos): array
{
    $validador = (new Validador())
        ->requerido('codigo', $datos['codigo'] ?? null)
        ->longitudMaxima('codigo', is_scalar($datos['codigo'] ?? null) ? (string) $datos['codigo'] : '', 30)
        ->requerido('nombre', $datos['nombre'] ?? null)
        ->longitudMaxima('nombre', is_scalar($datos['nombre'] ?? null) ? (string) $datos['nombre'] : '', 120)
        ->requerido('tipo', $datos['tipo'] ?? null);

    $errores = $validador->errores();
    $tipo = is_string($datos['tipo'] ?? null) ? $datos['tipo'] : '';
    $camposPorTipo = ServicioFactory::camposPorTipo();

    if (!array_key_exists($tipo, $camposPorTipo)) {
        $errores['tipo'][] = 'Selecciona un tipo de servicio válido.';
        return $errores;
    }

    foreach ($camposPorTipo[$tipo] as $campo => $reglas) {
        $valor = $datos[$campo] ?? null;
        $campoValidador = new Validador();

        if (($reglas['required'] ?? false) === true) {
            $campoValidador->requerido($campo, $valor);
        }

        $tieneValor = (is_scalar($valor) && trim((string) $valor) !== '');
        if ($tieneValor) {
            if (($reglas['step'] ?? '') === '1') {
                $campoValidador->enteroPositivo($campo, $valor);
            }
            $campoValidador->rango($campo, $valor, (float) $reglas['min'], (float) $reglas['max']);
        }

        foreach ($campoValidador->errores() as $nombreCampo => $mensajes) {
            $errores[$nombreCampo] = array_merge($errores[$nombreCampo] ?? [], $mensajes);
        }
    }

    return $errores;
}

/** Detecta si realmente se intentó subir una nueva imagen. */
function servicios_hay_imagen_nueva(array $archivos): bool
{
    return isset($archivos['imagen'])
        && is_array($archivos['imagen'])
        && (($archivos['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
}

function servicios_primer_error(array $errores, string $campo): ?string
{
    $mensajes = $errores[$campo] ?? null;
    return is_array($mensajes) && isset($mensajes[0]) && is_string($mensajes[0]) ? $mensajes[0] : null;
}
