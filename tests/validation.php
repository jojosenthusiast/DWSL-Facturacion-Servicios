<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
use App\Validation\Validador;

$validator = (new Validador())
    ->requerido('nombre', '   ')
    ->email('correo', 'invalido')
    ->longitudMaxima('texto', 'café', 3)
    ->numeroPositivo('tarifa', 'INF')
    ->enteroPositivo('cantidad', '2.5')
    ->rango('edad', 150, 0, 120);
if ($validator->esValido() || count($validator->errores()) !== 6) {
    throw new RuntimeException('The validator must accumulate all field errors.');
}
if (!(new Validador())->email('correo', 'ana@example.test')->numeroPositivo('tarifa', '1.25')->enteroPositivo('cantidad', '2')->esValido()) {
    throw new RuntimeException('Valid values should pass validation.');
}
echo "OK: validation rules and accumulated errors.\n";
