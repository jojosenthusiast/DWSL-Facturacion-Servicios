<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';
require __DIR__ . '/_comun.php';

use App\Repositories\ServicioRepositorio;
use App\Services\GestorImagenes;

$repositorio = new ServicioRepositorio(conexion_obtener());
$gestorImagenes = new GestorImagenes(dirname(__DIR__) . '/uploads');
$servicios = $repositorio->listar();
$raiz = dirname(__DIR__, 2);
$titulo = 'Servicios';
require $raiz . '/views/layout/encabezado.php';
?>
<section class="servicios-listado" aria-label="Listado de servicios">
    <div class="cabecera-pagina">
        <p>Administra los servicios facturables registrados.</p>
        <a class="boton" href="/servicios/crear.php">Crear servicio</a>
    </div>

    <?php if ($servicios === []): ?>
        <div class="vacio"><p>No hay servicios registrados.</p><a href="/servicios/crear.php">Registrar el primero</a></div>
    <?php else: ?>
        <div class="tabla-responsive tabla-servicios">
            <table>
                <caption>Servicios registrados</caption>
                <thead><tr><th scope="col">Imagen</th><th scope="col">Nombre</th><th scope="col">Tipo</th><th scope="col">Importe</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><img class="miniatura" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt="Miniatura del servicio <?= e($servicio->getNombre()) ?>"></td>
                        <td><strong><?= e($servicio->getNombre()) ?></strong><br><small><?= e($servicio->getCodigo()) ?></small></td>
                        <td><?= e($servicio->tipoLegible()) ?></td>
                        <td class="numerico">$<?= e(number_format($servicio->calcularImporte(), 2)) ?></td>
                        <td><span class="estado-servicio <?= $servicio->estaActivo() ? 'estado-activo' : 'estado-inactivo' ?>"><?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?></span></td>
                        <td><div class="acciones-tabla">
                            <a href="/servicios/ver.php?id=<?= e($servicio->getIdBd()) ?>">Ver</a>
                            <a href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar</a>
                            <a class="enlace-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar</a>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require $raiz . '/views/layout/pie.php'; ?>
