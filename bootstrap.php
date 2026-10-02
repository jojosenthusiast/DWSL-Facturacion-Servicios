<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Web/funciones.php';

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
