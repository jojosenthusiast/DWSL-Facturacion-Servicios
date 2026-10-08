<?php

declare(strict_types=1);
?>
<div class="cabecera-pagina listado-facturas-cabecera">
    <p>Consulta las facturas emitidas y sus importes.</p>
    <a class="boton" href="/facturas/crear.php">Crear factura</a>
</div>
<?php if ($facturas === []): ?>
    <section class="listado-facturas-vacio" aria-label="Sin facturas">
        <h2>No hay facturas registradas</h2>
        <p>Puedes registrar una nueva factura para empezar.</p>
        <a class="boton" href="/facturas/crear.php">Crear factura</a>
    </section>
<?php else: ?>
    <section class="listado-facturas" aria-labelledby="listado-facturas-titulo">
        <div class="listado-facturas-resumen">
            <h2 id="listado-facturas-titulo">Facturas emitidas</h2>
            <span><?= e(count($facturas)) ?> factura(s)</span>
        </div>
        <div class="tabla-responsive">
            <table class="tabla listado-facturas-tabla">
                <caption class="visualmente-oculto">Listado de facturas emitidas</caption>
                <thead><tr><th scope="col">Número</th><th scope="col">Cliente</th><th scope="col">Período</th><th scope="col" class="numerico">Total</th><th scope="col">Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td><strong>#<?= e($factura['id']) ?></strong></td>
                        <td><?= e($factura['cliente']) ?></td>
                        <td><strong><?= e($factura['periodo']) ?></strong><br><small><?= e($factura['rango']) ?></small></td>
                        <td class="numerico"><strong>$<?= e(sprintf('%.2f', $factura['total'])) ?></strong></td>
                        <td><a class="boton boton-secundario" href="/facturas/ver.php?id=<?= e($factura['id']) ?>">Ver detalle</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
