<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\Conexion;

$dsn = getenv('DWSL_TEST_DSN');
$usuario = getenv('DWSL_TEST_USER');
$clave = getenv('DWSL_TEST_PASSWORD');
if ($dsn === false || $usuario === false || $clave === false) {
    fwrite(STDERR, "Define DWSL_TEST_DSN, DWSL_TEST_USER y DWSL_TEST_PASSWORD para ejecutar la prueba MySQL/MariaDB.\n");
    exit(2);
}

$pdo = (new Conexion(['dsn' => $dsn, 'usuario' => $usuario, 'clave' => $clave]))->obtenerPDO();
$assert = static function (bool $condicion, string $mensaje): void {
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
};
$assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO debe lanzar excepciones.');
$assert($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC, 'PDO debe devolver filas asociativas.');
$assert((bool) $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES) === false, 'PDO debe usar prepared statements nativos.');

$conteos = $pdo->query(
    "SELECT (SELECT COUNT(*) FROM servicios) AS servicios,
            (SELECT COUNT(*) FROM clientes) AS clientes,
            (SELECT COUNT(*) FROM facturas) AS facturas,
            (SELECT COUNT(*) FROM factura_detalles) AS detalles"
)->fetch();
$assert((int) $conteos['servicios'] >= 9, 'El seed necesita al menos nueve servicios.');
$tipos = $pdo->query('SELECT tipo, COUNT(*) AS cantidad FROM servicios GROUP BY tipo')->fetchAll();
$porTipo = array_column($tipos, 'cantidad', 'tipo');
foreach (['medido', 'tarifa_plana', 'por_evento'] as $tipo) {
    $assert((int) ($porTipo[$tipo] ?? 0) >= 3, "El seed necesita tres servicios tipo {$tipo}.");
}
$assert((int) $conteos['clientes'] >= 1 && (int) $conteos['facturas'] >= 1 && (int) $conteos['detalles'] >= 1, 'El seed debe incluir clientes, facturas y detalles.');

$rechaza = static function (callable $accion, string $mensaje) use ($assert): void {
    try {
        $accion();
    } catch (PDOException) {
        return;
    }
    $assert(false, $mensaje);
};
$pdo->beginTransaction();
try {
    $rechaza(static fn () => $pdo->exec("INSERT INTO servicios (codigo,nombre,tipo,lectura_anterior,lectura_actual,tarifa_por_unidad) VALUES ('TEST-ZERO','Prueba','medido',0,1,0)"), 'La tarifa cero debe rechazarse.');
    $rechaza(static fn () => $pdo->exec("INSERT INTO servicios (codigo,nombre,tipo,cantidad_eventos,tarifa_por_evento) VALUES ('TEST-EVENT-ZERO','Prueba','por_evento',0,1)"), 'La cantidad cero debe rechazarse.');
    $rechaza(static fn () => $pdo->exec("INSERT INTO servicios (codigo,nombre,tipo,mensualidad) VALUES ('TEST-FLAT','Prueba','tarifa_plana',0)"), 'La mensualidad cero debe rechazarse.');
    $rechaza(static fn () => $pdo->exec("INSERT INTO facturas (cliente_id,fecha_inicio,fecha_fin) VALUES (1,'2026-02-01','2026-01-01')"), 'Un período inválido debe rechazarse.');
    $rechaza(static fn () => $pdo->exec("INSERT INTO facturas (cliente_id,fecha_inicio,fecha_fin) VALUES (1,'2026-01-01','2026-02-01')"), 'El cliente no debe duplicar exactamente el período.');
    $rechaza(static fn () => $pdo->exec("INSERT INTO factura_detalles (factura_id,servicio_id,descripcion,cantidad,precio_unitario,importe) VALUES (999999,1,'FK inválida',1,1,1)"), 'La llave foránea a factura debe protegerse.');
} finally {
    $pdo->rollBack();
}

fwrite(STDOUT, "OK: conexión PDO MySQL/MariaDB, opciones, seed, CHECK, UNIQUE y FK.\n");
