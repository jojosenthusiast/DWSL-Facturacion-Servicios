<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Factories\ServicioFactory;

$estado = servicios_formulario_extraer('crear');
$valores = $estado['valores'] ?? ['tipo' => '', 'activo' => '1'];
$errores = $estado['errores'] ?? [];
$tipos = ServicioFactory::tiposDisponibles();
$camposPorTipo = ServicioFactory::camposPorTipo();
$accion = '/servicios/guardar.php';
$textoBoton = 'Guardar servicio';
$imagenActualUrl = null;
$raiz = dirname(__DIR__, 2);
$titulo = 'Crear servicio';
require $raiz . '/views/layout/encabezado.php';
?>

    <p><a href="/servicios/index.php">← Volver a servicios</a></p>

    <?php require __DIR__ . '/_formulario.php'; ?>
<?php require $raiz . '/views/layout/pie.php'; ?>
