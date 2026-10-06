<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\Conexion;
use App\Exceptions\DatabaseException;
use App\Factories\ServicioFactory;
use App\Repositories\ServicioRepositorio;

$dsn = getenv('DWSL_TEST_DSN');
$usuario = getenv('DWSL_TEST_USER');
$clave = getenv('DWSL_TEST_PASSWORD');
if ($dsn === false || $usuario === false || $clave === false) {
    fwrite(STDERR, "Define DWSL_TEST_DSN, DWSL_TEST_USER y DWSL_TEST_PASSWORD para la BD de pruebas reconstruida con schema.sql y seed.sql.\n");
    exit(2);
}
$pdo = (new Conexion(['dsn' => $dsn, 'usuario' => $usuario, 'clave' => $clave]))->obtenerPDO();
$repositorio = new ServicioRepositorio($pdo);
$comprobar = static function (bool $condicion, string $caso): void {
    if (!$condicion) {
        throw new RuntimeException($caso);
    }
    echo "OK: {$caso}\n";
};
$errorSeguro = static function (callable $accion) use ($comprobar): void {
    try {
        $accion();
    } catch (DatabaseException $exception) {
        $comprobar($exception->getMessage() === DatabaseException::PUBLIC_MESSAGE
            && $exception->getPrevious() instanceof PDOException, 'PDO se conserva como causa interna y solo se expone mensaje común');
        return;
    }
    throw new RuntimeException('La BD debía rechazar la operación.');
};

$pdo->beginTransaction();
try {
    $semilla = $repositorio->listar();
    $comprobar(count($semilla) >= 9, 'lee los servicios existentes del seed de Carlos');
    $comprobar(count(array_unique(array_map(static fn ($servicio): string => $servicio->getTipo(), $semilla))) === 3, 'reconstruye los tres tipos del seed');

    $prefijo = 'TEST-' . bin2hex(random_bytes(4));
    $base = ['nombre' => "Prueba ' OR 1=1 --", 'activo' => '1', 'imagen' => 'prueba.webp'];
    $casos = [
        ['tipo' => 'medido', 'lectura_anterior' => '10', 'lectura_actual' => '12.5', 'tarifa_por_unidad' => '1.25'],
        ['tipo' => 'tarifa_plana', 'mensualidad' => '35.50'],
        ['tipo' => 'evento', 'cantidad_eventos' => '2', 'tarifa_por_evento' => '12.00'],
    ];
    $ids = [];
    foreach ($casos as $indice => $datos) {
        $servicio = ServicioFactory::desdeFormulario($datos + $base + ['codigo' => $prefijo . '-' . $indice]);
        $id = $repositorio->crear($servicio);
        $ids[] = $id;
        $leido = $repositorio->buscarPorId($id);
        $comprobar($id > 0 && $servicio->getIdBd() === $id, 'crear devuelve y asigna PK');
        $comprobar($leido !== null && get_class($leido) === get_class($servicio)
            && $leido->datosPersistibles() === $servicio->datosPersistibles(), 'leer reconstruye la subclase y todos sus datos');
        $comprobar($leido->getNombre() === $base['nombre'], 'prepared statements guardan texto con sintaxis SQL sin ejecutarlo');
        $comprobar($repositorio->actualizar($leido), 'actualizar sin cambios también reconoce que el registro existe');
    }

    $cambio = ServicioFactory::desdeFormulario([
        'tipo' => 'tarifa_plana', 'codigo' => $prefijo . '-0', 'nombre' => 'Servicio actualizado',
        'mensualidad' => '40.25', 'activo' => '0', 'imagen' => 'reemplazo.png',
    ]);
    $cambio->setIdBd($ids[0]);
    $comprobar($repositorio->actualizar($cambio), 'actualiza tipo, valores comunes, estado e imagen');
    $actualizado = $repositorio->buscarPorId($ids[0]);
    $comprobar($actualizado !== null && $actualizado->datosPersistibles() === $cambio->datosPersistibles()
        && $actualizado->calcularImporte() === 40.25, 'actualización conserva cálculos y limpia columnas del tipo anterior');
    $comprobar(!in_array($ids[0], array_map(static fn ($servicio): ?int => $servicio->getIdBd(), $repositorio->listar(true)), true), 'listado de activos excluye el servicio inactivo');

    $duplicado = ServicioFactory::desdeFormulario($casos[1] + $base + ['codigo' => $prefijo . '-1']);
    $errorSeguro(static fn () => $repositorio->crear($duplicado));
    $comprobar($duplicado->getIdBd() === null, 'crear fallido no asigna una PK falsa');

    // SRV-AGUA tiene detalles asociados en el seed: se respeta ON DELETE RESTRICT.
    $referenciado = current(array_filter($semilla, static fn ($servicio): bool => $servicio->getCodigo() === 'SRV-AGUA'));
    $comprobar($referenciado !== false, 'encuentra servicio facturado del seed');
    $errorSeguro(static fn () => $repositorio->eliminar($referenciado->getIdBd()));
    $comprobar($repositorio->buscarPorId($referenciado->getIdBd()) !== null, 'rechazo FK conserva el servicio facturado');

    foreach ($ids as $id) {
        $comprobar($repositorio->eliminar($id) && $repositorio->buscarPorId($id) === null, 'elimina servicio sin detalles asociados');
        $comprobar(!$repositorio->eliminar($id), 'eliminar un ID inexistente devuelve false');
    }
    $comprobar(!$repositorio->actualizar($cambio), 'actualizar un ID eliminado devuelve false');
    $comprobar(count($repositorio->listar()) === count($semilla), 'el ciclo CRUD no altera los registros del seed');
} finally {
    $pdo->rollBack();
}

echo "OK: pruebas de persistencia revertidas; datos de la BD intactos.\n";
