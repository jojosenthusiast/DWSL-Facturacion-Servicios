<?php

declare(strict_types=1);

/** [SEGURIDAD] Escape values before inserting them into HTML text or attributes. */
function e(mixed $valor): string
{
    if (!is_scalar($valor) && $valor !== null) {
        return '';
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** [SEGURIDAD] Start a session with restrictive cookie settings, only once. */
function sesion_iniciar(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent()) {
        throw new RuntimeException('No se pudo iniciar la sesión.');
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

/** [SEGURIDAD] Create a per-session CSRF token. */
function csrf_token(): string
{
    sesion_iniciar();
    if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

/** [SEGURIDAD] Compare CSRF tokens in constant time and reject non-string input. */
function csrf_verificar(mixed $token): bool
{
    sesion_iniciar();
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && is_string($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

/** [SEGURIDAD] Store a one-time message for the next request. */
function flash(string $tipo, string $mensaje): void
{
    sesion_iniciar();
    $_SESSION['_flash'][$tipo] = $mensaje;
}

/** [SEGURIDAD] Read and remove one flash message. */
function flash_obtener(string $tipo): ?string
{
    sesion_iniciar();
    $mensaje = $_SESSION['_flash'][$tipo] ?? null;
    unset($_SESSION['_flash'][$tipo]);
    return is_string($mensaje) ? $mensaje : null;
}

/** Reject unsafe requests before a route performs a state-changing action. */
function csrf_exigir_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_verificar($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        exit('Solicitud no válida. Actualiza la página e inténtalo de nuevo.');
    }
}
