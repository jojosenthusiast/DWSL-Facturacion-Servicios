<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Web/funciones.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

set_exception_handler(static function (Throwable $exception): void {
    error_log((string) $exception);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo \App\Exceptions\DatabaseException::PUBLIC_MESSAGE;
    exit(1);
});

sesion_iniciar();

/** [INYECCION-DEPENDENCIAS] Create PDO only when a route actually needs the database. */
function conexion_obtener(): \PDO
{
    $configPath = __DIR__ . '/config/config.php';
    if (!is_file($configPath)) {
        throw new RuntimeException('Configura la conexión local antes de usar la base de datos.');
    }

    $config = require $configPath;
    if (!is_array($config) || !isset($config['database']) || !is_array($config['database'])) {
        throw new RuntimeException('La configuración local de base de datos no es válida.');
    }

    return (new \App\Database\Conexion($config['database']))->obtenerPDO();
}
