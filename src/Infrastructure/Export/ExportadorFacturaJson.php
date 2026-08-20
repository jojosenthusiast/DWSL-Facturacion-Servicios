<?php

declare(strict_types=1);

namespace App\Infrastructure\Export;

use App\Factura;
use JsonException;
use RuntimeException;

/**
 * Clase encargada de exportar una factura a un archivo JSON.
 * 
 * Responsabilidades:
 * - Recibir una factura ya construida.
 * - Preparar los datos para exportarlos.
 * - Utilizar json_encode() y file_put_contents().
 * - Generar el archivo en storage/factura_demo.json.
 * - Manejar errores básicos de escritura o generación del JSON.
 * 
 * Este módulo NO calcula importes ni modifica la lógica de Factura.
 * Solamente recibe la información y la guarda.
 */
class ExportadorFacturaJson
{
    private string $rutaArchivo;

    /**
     * Constructor.
     * 
     * @param string $rutaArchivo Ruta donde se guardará el archivo JSON.
     *                            Por defecto: 'storage/factura_demo.json'
     */
    public function __construct(string $rutaArchivo = 'storage/factura_demo.json')
    {
        $this->rutaArchivo = $rutaArchivo;
    }

    /**
     * Exporta una factura a un archivo JSON.
     * 
     * @param Factura $factura La factura a exportar.
     * @return bool True si la exportación fue exitosa, False en caso contrario.
     */
    public function exportar(Factura $factura): bool
    {
        try {
            // 1. Preparar los datos de la factura
            $datosParaJson = $this->prepararDatos($factura);

            // 2. Convertir a JSON con formato legible
            $json = $this->convertirAJson($datosParaJson);

            // 3. Guardar en el archivo
            $this->guardarEnArchivo($json);

            return true;
        } catch (JsonException $e) {
            error_log("Error de codificación JSON: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("Error al guardar el archivo: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log("Error inesperado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepara un array con los datos de la factura.
     * 
     * @param Factura $factura
     * @return array
     */
    private function prepararDatos(Factura $factura): array
    {
        // Obtener cliente y período usando los métodos correctos
        $cliente = $factura->obtenerCliente();
        $periodo = $factura->obtenerPeriodo();
        $lineas = $factura->obtenerLineas(); // Ya tiene descripción e importe
        $total = $factura->calcularTotal();

        $datos = [
            'cliente' => [
                'id' => $cliente->getId(),
                'nombre' => $cliente->getNombre(),
                'correo' => $cliente->getCorreo(),
            ],
            'periodo' => [
                'fecha_inicio' => $periodo->getInicio()->format('Y-m-d'),
                'fecha_fin' => $periodo->getFin()->format('Y-m-d'),
                'etiqueta' => $periodo->obtenerEtiqueta(),
            ],
            'total' => number_format($total, 2, '.', ''),
            'servicios' => [],
        ];

        // Recorrer las líneas de la factura
        foreach ($lineas as $indice => $linea) {
            $datos['servicios'][] = [
                'indice' => $indice + 1,
                'descripcion' => $linea['descripcion'],
                'importe' => number_format($linea['importe'], 2, '.', ''),
            ];
        }

        return $datos;
    }

    /**
     * Convierte un array a JSON con formato legible.
     * 
     * @param array $datos
     * @return string
     * @throws JsonException
     */
    private function convertirAJson(array $datos): string
    {
        return json_encode($datos, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    /**
     * Guarda el contenido JSON en el archivo.
     * 
     * @param string $json
     * @return void
     * @throws RuntimeException
     */
    private function guardarEnArchivo(string $json): void
    {
        $directorio = dirname($this->rutaArchivo);

        // Crear el directorio si no existe
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0777, true) && !is_dir($directorio)) {
                throw new RuntimeException(
                    sprintf("No se pudo crear el directorio '%s'", $directorio)
                );
            }
        }

        // Escribir el archivo
        if (file_put_contents($this->rutaArchivo, $json, LOCK_EX) === false) {
            throw new RuntimeException(
                sprintf("No se pudo escribir el archivo '%s'", $this->rutaArchivo)
            );
        }
    }
}