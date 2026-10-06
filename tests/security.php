<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/Web/funciones.php';

if (e('<script>') !== '&lt;script&gt;') { throw new RuntimeException('HTML output was not escaped.'); }
if (e(['unexpected']) !== '') { throw new RuntimeException('Non-scalar output must not cause conversion warnings.'); }
$_SESSION = [];
$token = csrf_token();
if (strlen($token) !== 64 || !csrf_verificar($token) || csrf_verificar('incorrecto')) {
    throw new RuntimeException('CSRF token generation/verification failed.');
}
flash('success', 'Saved');
if (flash_obtener('success') !== 'Saved' || flash_obtener('success') !== null) {
    throw new RuntimeException('Flash message was not consumed exactly once.');
}
echo "OK: output escaping, CSRF, and one-time flash.\n";
