<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_exigir_post();
    flash('success', 'Protección CSRF verificada correctamente.');
    header('Location: /', true, 303);
    exit;
}

$mensaje = flash_obtener('success');
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facturación de servicios</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<main class="contenedor">
    <h1>Facturación de servicios</h1>
    <?php if ($mensaje !== null): ?><p class="mensaje" role="status"><?= e($mensaje) ?></p><?php endif; ?>
    <p>La base web está preparada. Las operaciones de escritura deben validar CSRF y usar consultas preparadas.</p>
    <form method="post" action="/" class="formulario">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit">Comprobar protección CSRF</button>
    </form>
</main>
</body>
</html>
