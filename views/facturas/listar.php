<?php

declare(strict_types=1);
?>
<p class="acciones"><a class="boton" href="/facturas/crear.php">Crear factura</a></p>

<?php if ($facturas === []): ?>
    <p>Todavía no hay facturas registradas.</p>
<?php else: ?>
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
                <td><a href="/facturas/ver.php?id=<?= e($factura['id']) ?>">Ver detalle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
