<?php

declare(strict_types=1);

namespace App\Database;

use App\Exceptions\DatabaseException;
use PDO;
use PDOException;

final class Conexion
{
    /** @param array{dsn:string, usuario:string, clave:string} $config */
    public function __construct(private readonly array $config)
    {
    }

    public function obtenerPDO(): PDO
    {
        try {
            return new PDO($this->config['dsn'], $this->config['usuario'], $this->config['clave'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            // Keep the original exception for internal diagnostics, but never show it to a user.
            throw new DatabaseException($exception);
        }
    }
}
