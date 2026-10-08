<?php

declare(strict_types=1);
?>
<div class="cabecera-pagina"><p>Consulta las facturas emitidas y sus importes.</p><a class="boton" href="/facturas/crear.php">Crear factura</a></div>

<?php if ($facturas === []): ?>
    <p>Todavía no hay facturas registradas.</p>
<?php else: ?>
    <div class="tabla-responsive">
        <table class="tabla">
        <caption>Facturas emitidas</caption>
        <thead>
        <tr>
            <th scope="col">Número</th>
            <th scope="col">Cliente</th>
            <th scope="col">Período</th>
            <th scope="col" class="numerico">Total</th>
            <th scope="col">Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($facturas as $factura): ?>
            <tr>
                <td>#<?= e($factura['id']) ?></td>
                <td><?= e($factura['cliente']) ?></td>
                <td><?= e($factura['periodo']) ?><br><small><?= e($factura['rango']) ?></small></td>
                <td class="numerico">$<?= e(sprintf('%.2f', $factura['total'])) ?></td>
                <td><a class="boton boton-secundario" href="/facturas/ver.php?id=<?= e($factura['id']) ?>">Ver detalle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
