<?php
/** @var array<string,mixed> $valores */
/** @var array<string,list<string>> $errores */
/** @var array<string,string> $tipos */
/** @var array<string,array<string,array>> $camposPorTipo */
/** @var string $accion */
/** @var string $textoBoton */
/** @var string|null $imagenActualUrl */
?>
<form method="post" action="<?= e($accion) ?>" enctype="multipart/form-data" class="formulario servicio-formulario">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

    <?php if (isset($errores['_general'])): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errores['_general'][0] ?? 'Revisa los datos ingresados.') ?></div>
    <?php endif; ?>

    <div class="campo">
        <label for="codigo">Código</label>
        <input id="codigo" name="codigo" maxlength="30" required value="<?= e($valores['codigo'] ?? '') ?>" aria-describedby="error-codigo">
        <?php if (($error = servicios_primer_error($errores, 'codigo')) !== null): ?><small id="error-codigo" class="error-campo"><?= e($error) ?></small><?php endif; ?>
    </div>

    <div class="campo">
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" maxlength="120" required value="<?= e($valores['nombre'] ?? '') ?>" aria-describedby="error-nombre">
        <?php if (($error = servicios_primer_error($errores, 'nombre')) !== null): ?><small id="error-nombre" class="error-campo"><?= e($error) ?></small><?php endif; ?>
    </div>

    <div class="campo">
        <label for="tipo">Tipo de servicio</label>
        <select id="tipo" name="tipo" required aria-describedby="error-tipo">
            <option value="">Selecciona un tipo</option>
            <?php foreach ($tipos as $valorTipo => $etiqueta): ?>
                <option value="<?= e($valorTipo) ?>" <?= (($valores['tipo'] ?? '') === $valorTipo) ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (($error = servicios_primer_error($errores, 'tipo')) !== null): ?><small id="error-tipo" class="error-campo"><?= e($error) ?></small><?php endif; ?>
    </div>

    <!-- [POLIMORFISMO] Los campos se generan desde ServicioFactory/modelos; no se codifican subtipos aquí. -->
    <?php foreach ($camposPorTipo as $tipoCampo => $definiciones): ?>
        <fieldset class="grupo-tipo" data-tipo="<?= e($tipoCampo) ?>">
            <legend>Datos de <?= e($tipos[$tipoCampo] ?? $tipoCampo) ?></legend>
            <?php foreach ($definiciones as $campo => $definicion): ?>
                <div class="campo">
                    <label for="<?= e($campo) ?>"><?= e($definicion['etiqueta']) ?></label>
                    <input
                        id="<?= e($campo) ?>"
                        name="<?= e($campo) ?>"
                        type="<?= e($definicion['tipo']) ?>"
                        min="<?= e($definicion['min']) ?>"
                        max="<?= e($definicion['max']) ?>"
                        step="<?= e($definicion['step']) ?>"
                        value="<?= e($valores[$campo] ?? '') ?>"
                        data-required="<?= ($definicion['required'] ?? false) ? '1' : '0' ?>"
                        aria-describedby="error-<?= e($campo) ?>"
                    >
                    <?php if (($error = servicios_primer_error($errores, $campo)) !== null): ?><small id="error-<?= e($campo) ?>" class="error-campo"><?= e($error) ?></small><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>

    <div class="campo campo-check">
        <input id="activo" name="activo" type="checkbox" value="1" <?= in_array($valores['activo'] ?? null, ['1', 1, true, 'on'], true) ? 'checked' : '' ?>>
        <label for="activo">Servicio activo</label>
    </div>

    <?php if ($imagenActualUrl !== null): ?>
        <div class="campo">
            <span>Imagen actual</span>
            <img class="imagen-detalle" src="<?= e($imagenActualUrl) ?>" alt="Imagen actual del servicio">
        </div>
    <?php endif; ?>

    <div class="campo">
        <label for="imagen"><?= $imagenActualUrl === null ? 'Imagen' : 'Reemplazar imagen' ?></label>
        <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="ayuda-imagen error-imagen">
        <small id="ayuda-imagen">JPEG, PNG o WEBP. Máximo 2 MB.</small>
        <?php if (($error = servicios_primer_error($errores, 'imagen')) !== null): ?><small id="error-imagen" class="error-campo"><?= e($error) ?></small><?php endif; ?>
    </div>

    <div class="acciones-formulario">
        <button type="submit"><?= e($textoBoton) ?></button>
        <a class="boton-secundario" href="/servicios/index.php">Cancelar</a>
    </div>
</form>
<script>
(() => {
    const selector = document.getElementById('tipo');
    const grupos = [...document.querySelectorAll('.grupo-tipo')];
    const sincronizar = () => {
        grupos.forEach((grupo) => {
            const activo = grupo.dataset.tipo === selector.value;
            grupo.hidden = !activo;
            grupo.querySelectorAll('input').forEach((input) => {
                input.disabled = !activo;
                input.required = activo && input.dataset.required === '1';
            });
        });
    };
    selector.addEventListener('change', sincronizar);
    sincronizar();
})();
</script>
