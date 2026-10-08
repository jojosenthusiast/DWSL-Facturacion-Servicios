<?php

declare(strict_types=1);

$lineas = $factura->obtenerLineas();
?>
<section class="reporte factura-detalle">
    <h2>Factura #<?= e($id) ?></h2>

    <dl class="reporte-datos">
        <dt>Cliente</dt>
        <dd><?= e($factura->obtenerCliente()->getNombre()) ?>
            <small>(<?= e($factura->obtenerCliente()->getId()) ?> · <?= e($factura->obtenerCliente()->getCorreo()) ?>)</small>
        </dd>
        <dt>Período</dt>
        <dd><?= e($factura->obtenerPeriodo()->obtenerEtiqueta()) ?> <small><?= e($rango) ?></small></dd>
    </dl>

    <hr class="separador">

    <?php if ($lineas === []): ?>
        <p>La factura no tiene servicios asociados.</p>
    <?php else: ?>
        <div class="tabla-responsive">
            <table class="tabla">
        <caption>Servicios incluidos</caption>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Descripción</th>
                <th scope="col" class="numerico">Importe</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($lineas as $indice => $linea): ?>
                <tr>
                    <td><?= e($indice + 1) ?></td>
                    <td><?= e($linea['descripcion']) ?></td>
                    <td class="numerico">$<?= e(sprintf('%.2f', $linea['importe'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2" scope="row">Total</th>
                <td class="numerico">$<?= e(sprintf('%.2f', $factura->calcularTotal())) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</section>

<p class="acciones">
    <a class="boton" href="/reporte/index.php">Ver reporte web</a>
    <a class="boton boton-secundario" href="/facturas/index.php">Volver al listado</a>
</p>
