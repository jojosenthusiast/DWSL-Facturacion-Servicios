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

    <header class="cabecera-pagina">
        <div><h2>Servicios</h2><p>Administración de servicios facturables.</p></div>
        <a class="boton" href="/servicios/crear.php">Crear servicio</a>
    </header>

    <?php if ($servicios === []): ?>
        <div class="vacio"><p>No hay servicios registrados.</p><a href="/servicios/crear.php">Registrar el primero</a></div>
    <?php else: ?>
        <div class="tabla-responsive">
            <table>
                <thead><tr><th>Imagen</th><th>Nombre</th><th>Tipo</th><th>Importe</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><img class="miniatura" src="<?= e(servicios_imagen_url($gestorImagenes, $servicio->getImagen())) ?>" alt=""></td>
                        <td><strong><?= e($servicio->getNombre()) ?></strong><br><small><?= e($servicio->getCodigo()) ?></small></td>
                        <td><?= e($servicio->tipoLegible()) ?></td>
                        <td>$<?= e(number_format($servicio->calcularImporte(), 2)) ?></td>
                        <td><?= $servicio->estaActivo() ? 'Activo' : 'Inactivo' ?></td>
                        <td class="acciones-tabla">
                            <a href="/servicios/ver.php?id=<?= e($servicio->getIdBd()) ?>">Ver</a>
                            <a href="/servicios/editar.php?id=<?= e($servicio->getIdBd()) ?>">Editar</a>
                            <a class="enlace-peligro" href="/servicios/eliminar.php?id=<?= e($servicio->getIdBd()) ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php require $raiz . '/views/layout/pie.php'; ?>
