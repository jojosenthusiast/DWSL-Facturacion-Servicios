<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Exceptions\ImagenException;
use App\Services\GestorImagenes;

$dirUploads = sys_get_temp_dir() . '/uploads_prueba';
@mkdir($dirUploads, 0755, true);
copy(dirname(__DIR__) . '/public/uploads/imagen-predeterminada.png', $dirUploads . '/imagen-predeterminada.png');

$gestor = new GestorImagenes($dirUploads);
$fallos = 0;

function verificar(string $mensaje, bool $condicion): void
{
    global $fallos;
    if ($condicion) {
        echo "[OK] $mensaje\n";
    } else {
        echo "[FALLO] $mensaje\n";
        $fallos++;
    }
}

function archivoFalso(string $ruta, array $override = []): array
{
    return array_merge([
        'name' => 'foto_original.png',
        'type' => 'image/png',
        'tmp_name' => $ruta,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($ruta),
    ], $override);
}

// 1. Archivo inválido (no es imagen) debe rechazarse.
$txt = tempnam(sys_get_temp_dir(), 'img') . '.png';
file_put_contents($txt, 'esto no es una imagen');
try {
    $gestor->guardar(archivoFalso($txt));
    verificar('rechaza archivo inválido', false);
} catch (ImagenException) {
    verificar('rechaza archivo inválido', true);
}

// 2. Archivo demasiado grande debe rechazarse.
$grande = tempnam(sys_get_temp_dir(), 'img') . '.png';
file_put_contents($grande, random_bytes(2 * 1024 * 1024 + 1024));
verificar('fixture grande > 2MB', filesize($grande) > 2 * 1024 * 1024);
try {
    $gestor->guardar(archivoFalso($grande));
    verificar('rechaza archivo mayor a 2 MB', false);
} catch (ImagenException) {
    verificar('rechaza archivo mayor a 2 MB', true);
}

// 3. Error de subida debe rechazarse.
try {
    $gestor->guardar(archivoFalso($grande, ['error' => UPLOAD_ERR_PARTIAL]));
    verificar('rechaza UPLOAD_ERR distinto de OK', false);
} catch (ImagenException) {
    verificar('rechaza UPLOAD_ERR distinto de OK', true);
}

// 4. Subida válida: el nombre lo genera el sistema.
$pngValida = tempnam(sys_get_temp_dir(), 'img') . '.png';
$img = imagecreatetruecolor(10, 10);
imagepng($img, $pngValida);
imagedestroy($img);
$nombre1 = $gestor->guardar(archivoFalso($pngValida));
verificar('nombre generado por el sistema (no el original)', $nombre1 !== 'foto_original.png' && preg_match('/^[0-9a-f]{32}\.png$/', $nombre1) === 1);
verificar('archivo guardado en uploads', is_file($dirUploads . '/' . $nombre1));

// 5. Mostrar imagen: existente y por defecto.
verificar('mostrar imagen guardada', $gestor->obtenerImagen($nombre1) === $nombre1);
verificar('imagen por defecto si no hay propia', $gestor->obtenerImagen(null) === 'imagen-predeterminada.png');
verificar('imagen por defecto si el archivo no existe', $gestor->obtenerImagen('no-existe.png') === 'imagen-predeterminada.png');

// 6. Reemplazar: la anterior se borra y no queda huérfana.
$pngValida2 = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
$img2 = imagecreatetruecolor(10, 10);
imagejpeg($img2, $pngValida2);
imagedestroy($img2);
$nombre2 = $gestor->reemplazar($nombre1, archivoFalso($pngValida2));
verificar('reemplazar genera nombre nuevo', $nombre2 !== $nombre1 && str_ends_with($nombre2, '.jpg'));
verificar('reemplazar no deja archivo huérfano', !is_file($dirUploads . '/' . $nombre1) && is_file($dirUploads . '/' . $nombre2));

// 7. Eliminar servicio elimina la imagen.
$gestor->eliminar($nombre2);
verificar('eliminar borra la imagen', !is_file($dirUploads . '/' . $nombre2));
verificar('imagen por defecto sobrevive', is_file($dirUploads . '/imagen-predeterminada.png'));

// Limpieza
foreach (glob($dirUploads . '/*') as $f) { unlink($f); }
rmdir($dirUploads);

echo $fallos === 0 ? "\nTodas las pruebas pasaron.\n" : "\n$fallos prueba(s) fallaron.\n";
exit($fallos === 0 ? 0 : 1);
