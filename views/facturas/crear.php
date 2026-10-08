<?php

declare(strict_types=1);

$errorGeneral = formulario_error($errores, 'general');
$errorCliente = formulario_error($errores, 'cliente_id');
$errorPeriodo = formulario_error($errores, 'periodo');
$errorServicios = formulario_error($errores, 'servicios');
?>
<?php if ($errorGeneral !== null): ?>
    <p class="alerta alerta-error mensaje-error" role="alert"><?= e($errorGeneral) ?></p>
<?php endif; ?>

<p class="texto-ayuda">Selecciona el cliente, el periodo y los servicios que deseas facturar.</p>
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

    <label class="campo">
        <span>Cliente</span>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">Selecciona un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <?php $seleccionado = formulario_valor($valores, 'cliente_id') === (string) $cliente['id']; ?>
                <option value="<?= e($cliente['id']) ?>"<?= $seleccionado ? ' selected' : '' ?>>
                    <?= e($cliente['nombre'] . ' (' . $cliente['codigo'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($errorCliente !== null): ?><span class="error-campo"><?= e($errorCliente) ?></span><?php endif; ?>
    </label>

    <label class="campo">
        <span>Período de facturación (mes)</span>
        <input type="month" name="periodo" id="periodo"
               value="<?= e(formulario_valor($valores, 'periodo')) ?>" required>
        <?php if ($errorPeriodo !== null): ?><span class="error-campo"><?= e($errorPeriodo) ?></span><?php endif; ?>
    </label>

    <fieldset class="campo">
        <legend>Servicios a facturar</legend>
        <?php if ($servicios === []): ?>
            <p>No hay servicios activos disponibles.</p>
        <?php else: ?>
            <ul class="opciones">
                <?php foreach ($servicios as $servicio): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="servicios[]" value="<?= e($servicio['id']) ?>"
                                <?= formulario_marcado($valores, 'servicios', (string) $servicio['id']) ? ' checked' : '' ?>>
                            <?= e($servicio['nombre']) ?>
                            <small>(<?= e($servicio['codigo']) ?> · <?= e(str_replace('_', ' ', $servicio['tipo'])) ?>)</small>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($errorServicios !== null): ?><span class="error-campo"><?= e($errorServicios) ?></span><?php endif; ?>
    </fieldset>

    <p class="acciones">
        <button type="submit">Guardar factura</button>
        <a class="boton boton-secundario" href="/facturas/index.php">Cancelar</a>
    </p>
</form>
