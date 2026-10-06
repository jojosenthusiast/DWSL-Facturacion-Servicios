<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\Conexion;
use App\Exceptions\DatabaseException;

$dsn = getenv('DWSL_TEST_DSN');
$usuario = getenv('DWSL_TEST_USER');
$clave = getenv('DWSL_TEST_PASSWORD');
if ($dsn === false || $usuario === false || $clave === false) {
    fwrite(STDERR, "Define DWSL_TEST_DSN, DWSL_TEST_USER y DWSL_TEST_PASSWORD.\n");
    exit(2);
}
$pdo = (new Conexion(['dsn' => $dsn, 'usuario' => $usuario, 'clave' => $clave]))->obtenerPDO();
if ($pdo->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION
    || $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) !== PDO::FETCH_ASSOC
    || (bool) $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES)) {
    throw new RuntimeException('PDO options do not match the database contract.');
}
$result = $pdo->query('SELECT 1 AS valor')->fetch();
if ($result !== ['valor' => 1] && $result !== ['valor' => '1']) {
    throw new RuntimeException('PDO fetch mode is not associative.');
}
echo "OK: PDO connection options and associative fetch mode.\n";

try {
    (new Conexion(['dsn' => 'mysql:host=127.0.0.1;port=1;dbname=missing;connect_timeout=1', 'usuario' => 'invalid', 'clave' => 'sensitive-test-value']))->obtenerPDO();
    throw new RuntimeException('An unreachable database should fail.');
} catch (DatabaseException $exception) {
    if ($exception->getMessage() !== DatabaseException::PUBLIC_MESSAGE
        || str_contains($exception->getMessage(), 'sensitive-test-value')) {
        throw new RuntimeException('Database failures must expose only the generic public message.');
    }
}
echo "OK: database errors expose no PDO details.\n";
