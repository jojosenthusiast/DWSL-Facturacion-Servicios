# Evidencias de interfaz - Caso F, Fase 2

Estas evidencias deben ser capturas REALES de la aplicacion ejecutada en el navegador. No agregar imagenes de relleno ni credenciales.

## Preparacion

1. Configurar `config/config.php`, MySQL/MariaDB y cargar `database/schema.sql` y `database/seed.sql` siguiendo el README.
2. Ejecutar `composer install` y `php -S localhost:8000 -t public` desde la raiz.
3. Abrir cada ruta y tomar capturas usando `Win + Shift + S`, guardar en PNG dentro de esta carpeta con el nombre exacto que se indica.
4. Para detalle, editar y eliminar, usa un ID de servicio existente, por ejemplo `id=1` si existe. No confirmes la eliminacion solamente para la captura.
5. Para detalle de factura, usa un ID real, por ejemplo `id=1` si existe.

## Ocho capturas requeridas

| Archivo PNG | Ruta de la aplicacion | Evidencia |
| --- | --- | --- |
| 01-inicio.png | `/` | Panel Caso F y navegacion |
| 02-servicios-listado.png | `/servicios/index.php` | Servicios registrados |
| 03-servicios-crear.png | `/servicios/crear.php` | Formulario de alta |
| 04-servicios-detalle.png | `/servicios/ver.php?id=ID` | Detalle del servicio |
| 05-servicios-editar.png | `/servicios/editar.php?id=ID` | Formulario de edicion |
| 06-servicios-eliminar.png | `/servicios/eliminar.php?id=ID` | Confirmacion sin borrar |
| 07-facturas.png | `/facturas/index.php` | Lista de facturas |
| 08-reporte.png | `/reporte/index.php` | Reporte de facturacion |

## Evidencias adicionales aconsejadas

- `09-factura-crear.png`: `/facturas/crear.php`
- `10-factura-detalle.png`: `/facturas/ver.php?id=ID`
- `11-movil.png`: vista responsive desde herramientas del navegador (375 px)

## Diagramas

- [Modelo relacional](../modelo-relacional.md)
- [Clases y tablas](../clases-y-tablas.md)

Se incorporan al informe final con un pie explicativo por captura.