<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

$id = servicios_id_desde_peticion($_GET['id'] ?? null);
if ($id === null) {
    servicios_404();
}

$servicio = (new ServicioRepositorio(conexion_obtener()))->buscarPorId($id);
if ($servicio === null) {
    servicios_404();
}

$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$datosEspecificos = $servicio->datosEspecificos();
$camposEspecificos = $servicio->camposEspecificos();
$raiz = dirname(__DIR__, 2);
$titulo = $servicio->getNombre();
require $raiz . '/views/layout/encabezado.php';
?>

    <p><a href="/servicios/index.php">← Volver a servicios</a></p>

    <div class="detalle-servicio">
        <img class="imagen-detalle" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt="Imagen de <?= e($servicio->getNombre()) ?>">
        <div>
            <h2><?= e($servicio->getNombre()) ?></h2>
            <dl class="lista-datos">
                <dt>Código</dt><dd><?= e($servicio->getCodigo()) ?></dd>
                <dt>Tipo</dt><dd><?= e($servicio->tipoLegible()) ?></dd>
                <dt>Estado</dt><dd><?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?></dd>
                <!-- [POLIMORFISMO] El detalle consume la API del modelo sin reconocer tipos concretos. -->
                <?php foreach ($datosEspecificos as $campo => $valor): ?>
                    <dt><?= e($camposEspecificos[$campo]['etiqueta'] ?? $campo) ?></dt><dd><?= e($valor) ?></dd>
                <?php endforeach; ?>
                <dt>Importe</dt><dd><strong>$<?= e(number_format($servicio->calcularImporte(), 2)) ?></strong></dd>
            </dl>
            <div class="acciones-formulario">
                <a class="boton" href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar</a>
                <a class="boton-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar</a>
            </div>
        </div>
    </div>
<?php require $raiz . '/views/layout/pie.php'; ?>
