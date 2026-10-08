<?php

declare(strict_types=1);

// El controlador o layout obtiene los mensajes flash; este partial solo los muestra.
?>
<?php if (isset($mensajeExito) && $mensajeExito !== ''): ?>
    <p class="mensaje alerta alerta-exito" role="status"><?= e($mensajeExito) ?></p>
<?php endif; ?>
<?php if (isset($mensajeError) && $mensajeError !== ''): ?>
    <p class="mensaje mensaje--error alerta alerta-error mensaje-error" role="alert"><?= e($mensajeError) ?></p>
<?php endif; ?>
