<?php

declare(strict_types=1);

// [POLIMORFISMO] obtenerLineas() y calcularTotal() sin condicionales por tipo.
?>
<?php if ($entradas === []): ?>
    <p>Todavía no hay facturas guardadas. Puedes crear una en <a href="/facturas/crear.php">Nueva factura</a>.</p>
<?php else: ?>
    <p class="reporte-resumen">Facturas en el reporte: <strong><?= e(count($entradas)) ?></strong></p>
    <?php foreach ($entradas as $entrada): ?>
        <?php $factura = $entrada['factura']; ?>
        <article class="reporte reporte-factura">
            <h2>FACTURA MENSUAL #<?= e($entrada['id']) ?></h2>

            <p class="reporte-dato"><strong>Cliente:</strong> <?= e($factura->obtenerCliente()->getNombre()) ?></p>
            <p class="reporte-dato"><strong>Periodo:</strong> <?= e($factura->obtenerPeriodo()->obtenerEtiqueta()) ?></p>

            <hr class="separador">

            <?php foreach ($factura->obtenerLineas() as $indice => $linea): ?>
                <div class="linea">
                    <p><strong>Servicio <?= e($indice + 1) ?></strong></p>
                    <p>Descripción: <?= e($linea['descripcion']) ?></p>
                    <p>Importe: $<?= e(sprintf('%.2f', $linea['importe'])) ?></p>
                </div>
            <?php endforeach; ?>

            <hr class="separador">

            <p class="total">Total: $<?= e(sprintf('%.2f', $factura->calcularTotal())) ?></p>
            <p class="acciones"><a class="boton boton-secundario" href="/facturas/ver.php?id=<?= e($entrada['id']) ?>">Ver detalle de la factura</a></p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
