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
<section class="ficha-servicio" aria-label="Detalle del servicio">
    <div class="ficha-superior">
        <a class="enlace-volver" href="/servicios/index.php">&larr; Volver a servicios</a>
        <span class="estado-servicio <?= $servicio->estaActivo() ? 'estado-activo' : 'estado-inactivo' ?>">
            <?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?>
        </span>
    </div>

    <div class="ficha-contenido">
        <div class="ficha-imagen">
            <img class="imagen-detalle" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt="Imagen de <?= e($servicio->getNombre()) ?>">
        </div>
        <div class="ficha-informacion">
            <p class="ficha-categoria"><?= e($servicio->tipoLegible()) ?></p>
            <p class="ficha-codigo">C&oacute;digo: <strong><?= e($servicio->getCodigo()) ?></strong></p>
            <h2 class="ficha-subtitulo">Informaci&oacute;n del servicio</h2>
            <dl class="ficha-datos">
                <div><dt>Tipo</dt><dd><?= e($servicio->tipoLegible()) ?></dd></div>
                <?php foreach ($datosEspecificos as $campo => $valor): ?>
                    <div>
                        <dt><?= e($camposEspecificos[$campo]['etiqueta'] ?? $campo) ?></dt>
                        <dd><?= e($valor) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
            <div class="ficha-total">
                <span>Importe calculado</span>
                <strong>$<?= e(number_format($servicio->calcularImporte(), 2)) ?></strong>
            </div>
            <div class="acciones-formulario ficha-acciones">
                <a class="boton" href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar servicio</a>
                <a class="boton-secundario enlace-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar servicio</a>
            </div>
        </div>
    </div>
</section>
<?php require $raiz . '/views/layout/pie.php'; ?>
