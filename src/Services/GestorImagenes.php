<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ImagenException;

/**
 * [VALIDACION] [SEGURIDAD] Manejo completo de imágenes subidas por el usuario.
 *
 * Permite subir, reemplazar, eliminar y resolver la imagen por defecto del sistema.
 * Leonel puede usarlo directamente en guardar, actualizar y eliminar.
 */
final class GestorImagenes
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    private const PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const IMAGEN_POR_DEFECTO = 'imagen-predeterminada.png';

    public function __construct(private string $directorio)
    {
        $this->directorio = rtrim($directorio, '/\\');

        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0755, true);
        }
    }

    /** Guarda un archivo de $_FILES y devuelve el nombre generado para la BD. */
    public function guardar(array $archivo): string // [VALIDACION] [SEGURIDAD]
    {
        if (!isset($archivo['error'], $archivo['size'], $archivo['tmp_name'])) {
            throw new ImagenException('No se recibió ningún archivo válido.');
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new ImagenException('No se pudo subir la imagen.');
        }

        if (!is_int($archivo['size']) || $archivo['size'] <= 0) {
            throw new ImagenException('El archivo de imagen está vacío.');
        }

        if ($archivo['size'] > self::MAX_BYTES) {
            throw new ImagenException('La imagen supera el tamaño máximo de 2 MB.');
        }

        // [SEGURIDAD] Se valida el tipo real con finfo, no la extensión del nombre.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);

        if (!is_string($mime)) {
            throw new ImagenException('No se pudo determinar el tipo de archivo.');
        }

        $ext = self::PERMITIDOS[$mime]
            ?? throw new ImagenException('Formato no permitido. Solo JPEG, PNG o WEBP.');

        // [SEGURIDAD] Nombre único generado por el sistema, nunca el nombre original.
        $nombre = bin2hex(random_bytes(16)) . '.' . $ext;

        $this->moverArchivo($archivo['tmp_name'], $this->rutaSegura($nombre));

        return $nombre;
    }

    /** Elimina una imagen previa (si existe y no es la predeterminada). */
    public function eliminar(?string $nombre): void // [SEGURIDAD]
    {
        if ($nombre === null || $nombre === '' || $nombre === self::IMAGEN_POR_DEFECTO) {
            return;
        }

        $ruta = $this->rutaSegura($nombre);

        if (is_file($ruta)) {
            unlink($ruta);
        }
    }

    /** Reemplaza una imagen existente: guarda la nueva y borra la anterior. */
    public function reemplazar(?string $nombreAnterior, array $archivo): string // [VALIDACION] [SEGURIDAD]
    {
        $nombreNuevo = $this->guardar($archivo);

        if ($nombreAnterior !== null && $nombreAnterior !== '' && $nombreAnterior !== $nombreNuevo) {
            $this->eliminar($nombreAnterior);
        }

        return $nombreNuevo;
    }

    /** Devuelve el nombre de la imagen a mostrar, o la predeterminada si no hay. */
    public function obtenerImagen(?string $nombre): string
    {
        if ($nombre !== null && $nombre !== '' && is_file($this->rutaSegura($nombre))) {
            return basename($nombre);
        }

        return self::IMAGEN_POR_DEFECTO;
    }

    /** Ruta física segura dentro del directorio de subida (evita path traversal). */
    public function rutaSegura(string $nombre): string // [SEGURIDAD]
    {
        $nombreSeguro = basename($nombre);

        return $this->directorio . DIRECTORY_SEPARATOR . $nombreSeguro;
    }

    public function rutaImagen(?string $nombre): string
    {
        return $this->rutaSegura($this->obtenerImagen($nombre));
    }

    public static function nombrePorDefecto(): string
    {
        return self::IMAGEN_POR_DEFECTO;
    }

    private function moverArchivo(string $origen, string $destino): void
    {
        $movido = is_uploaded_file($origen)
            ? move_uploaded_file($origen, $destino)
            : rename($origen, $destino); // Permite pruebas desde la CLI.

        if (!$movido) {
            throw new ImagenException('No se pudo guardar la imagen.');
        }
    }
}
