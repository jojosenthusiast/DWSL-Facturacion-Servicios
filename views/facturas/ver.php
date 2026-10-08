<?php

declare(strict_types=1);

$lineas = $factura->obtenerLineas();
$cliente = $factura->obtenerCliente();
$periodo = $factura->obtenerPeriodo();
?>
<section class="ficha-factura" aria-label="Detalle de la factura">
    <div class="ficha-factura-cabecera">
        <div>
            <p class="ficha-factura-sobretitulo">Comprobante de servicios</p>
            <h2>Detalle de la factura</h2>
            <p class="ficha-factura-identificador">Número #<?= e($id) ?></p>
        </div>
        <span class="ficha-factura-etiqueta">Registrada</span>
    </div>

    <div class="ficha-factura-metadatos">
        <div class="ficha-factura-dato">
            <span class="ficha-factura-label">Cliente</span>
            <strong><?= e($cliente->getNombre()) ?></strong>
            <span><?= e($cliente->getId()) ?></span>
            <span class="ficha-factura-correo"><?= e($cliente->getCorreo()) ?></span>
        </div>
        <div class="ficha-factura-dato">
            <span class="ficha-factura-label">Período de facturación</span>
            <strong><?= e($periodo->obtenerEtiqueta()) ?></strong>
            <span><?= e($rango) ?></span>
        </div>
    </div>

    <section class="ficha-factura-conceptos" aria-labelledby="conceptos-factura">
        <div class="ficha-factura-seccion">
            <h3 id="conceptos-factura">Servicios facturados</h3>
            <span><?= e(count($lineas)) ?> concepto(s)</span>
        </div>
        <?php if ($lineas === []): ?>
            <p class="ficha-factura-vacio">Esta factura no tiene servicios asociados.</p>
        <?php else: ?>
            <div class="tabla-responsive">
                <table class="tabla ficha-factura-tabla">
                    <caption class="visualmente-oculto">Conceptos incluidos en la factura</caption>
                    <thead>
                        <tr><th scope="col">#</th><th scope="col">Descripción</th><th scope="col" class="numerico">Importe</th></tr>
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
                </table>
            </div>
        <?php endif; ?>
    </section>
    <div class="ficha-factura-total" aria-label="Total de la factura">
        <span>Total facturado</span>
        <strong>$<?= e(sprintf('%.2f', $factura->calcularTotal())) ?></strong>
    </div>
</section>
<nav class="acciones ficha-factura-acciones" aria-label="Acciones de factura">
    <a class="boton" href="/reporte/index.php">Ver reporte web</a>
    <a class="boton boton-secundario" href="/facturas/index.php">Volver al listado</a>
</nav>
