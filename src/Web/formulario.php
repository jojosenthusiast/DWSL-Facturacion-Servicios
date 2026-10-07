<?php

declare(strict_types=1);

function formulario_guardar(array $datos, array $errores = []): void
{
    sesion_iniciar();
    $_SESSION['_formulario'] = ['datos' => $datos, 'errores' => $errores];
}

function formulario_obtener(): array
{
    sesion_iniciar();

    $formulario = $_SESSION['_formulario'] ?? null;
    unset($_SESSION['_formulario']);

    if (!is_array($formulario)) {
        return ['datos' => [], 'errores' => []];
    }

    $datos = $formulario['datos'] ?? null;
    $errores = $formulario['errores'] ?? null;

    return [
        'datos' => is_array($datos) ? $datos : [],
        'errores' => is_array($errores) ? $errores : [],
    ];
}

function formulario_error(array $errores, string $campo): ?string
{
    $mensajes = $errores[$campo] ?? null;
    if (!is_array($mensajes) || $mensajes === []) {
        return null;
    }

    $primero = reset($mensajes);

    return is_string($primero) ? $primero : null;
}

function formulario_valor(array $datos, string $campo, string $predeterminado = ''): string
{
    $valor = $datos[$campo] ?? null;

    return is_scalar($valor) ? (string) $valor : $predeterminado;
}

function formulario_marcado(array $datos, string $campo, string $valor): bool
{
    $seleccion = $datos[$campo] ?? null;
    if (!is_array($seleccion)) {
        return false;
    }

    return in_array($valor, array_map('strval', $seleccion), true);
}
