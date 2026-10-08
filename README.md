# Facturación de Servicios — Caso F · Fase 2

Aplicación web de facturación de servicios desarrollada en **PHP 8.1+**, con programación orientada a objetos, persistencia en **MySQL/MariaDB**, interfaces HTML semánticas y CSS propio adaptable. Proyecto académico de **Desarrollo Web con Software Libre**.

## Integrantes

- Coto Beltran, Angel Eduardo
- Fernando Emilio Valle Bernal
- Jorge Alexis Ramos Ramos
- Murgas Juarez, Carlos Gabriel
- Munguia Noyola, Leonel Alexander
- Milton Josue Ramirez Gongora

## Descripción y funcionalidades

El **Caso F** permite administrar servicios de distinta naturaleza: **medidos** (consumo × tarifa), **tarifa plana** (mensualidad fija) y **por evento** (cantidad × tarifa). La aplicación incluye:

- **Inicio:** descripción del caso y accesos a los módulos.
- **Servicios:** listado, alta, detalle, edición y eliminación; validación de datos, carga de imágenes y conservación de datos del formulario si hay errores.
- **Facturas:** creación de facturas con cliente, período y servicios activos; consulta de facturas emitidas y su detalle.
- **Reporte:** visualización de facturas, líneas e importes, y total.

La aplicación utiliza repositorios para la persistencia, una fábrica para reconstruir servicios y polimorfismo mediante el contrato `Facturable`. Se incluyen protección CSRF en solicitudes de escritura, consultas preparadas y escape de datos en la salida HTML.

## Requisitos

- **PHP 8.1 o superior**, disponible desde la terminal, con extensiones **PDO** y **pdo_mysql** habilitadas.
- **MySQL o MariaDB** en ejecución, con usuario que pueda crear/usar la base de datos o con una base previamente preparada.
- **Composer** instalado.
- Navegador web moderno.

Comprobación desde CMD o terminal:

```bash
php -v
php -m
composer --version
mysql --version
```

En `php -m` comprueba que figuren `PDO` y `pdo_mysql`. Si Windows indica que `php` no se reconoce, instala/configura PHP o añade su carpeta al `PATH` antes de seguir.

## Instalación desde cero

1. Clona el proyecto y entra en su carpeta:

   ```bash
   git clone https://github.com/jojosenthusiast/DWSL-Facturacion-Servicios.git
   cd DWSL-Facturacion-Servicios
   ```

2. Instala dependencias y genera el autoload PSR-4:

   ```bash
   composer install
   ```

3. Copia la configuración de ejemplo **sin modificar el archivo original**:

   **Windows (CMD):**

   ```cmd
   copy config\config.example.php config\config.php
   ```

   **Linux/macOS:**

   ```bash
   cp config/config.example.php config/config.php
   ```

4. Edita `config/config.php` para indicar la conexión local:

   ```php
   <?php
   declare(strict_types=1);

   return [
       'database' => [
           'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=facturacion_servicios;charset=utf8mb4',
           'usuario' => 'TU_USUARIO',
           'clave' => 'TU_CLAVE',
       ],
   ];
   ```

   `config/config.php` contiene credenciales locales: **no lo subas a Git**.

5. Crea las tablas y carga los datos de prueba. Desde la **raíz del proyecto**, ejecuta en ese orden:

   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```

   Sustituye `root` por el usuario con permisos adecuados. Si no tienes el ejecutable `mysql` en el `PATH`, abre estos archivos y ejecútalos en este orden desde tu cliente MySQL/MariaDB (por ejemplo, phpMyAdmin o MySQL Workbench). **No ejecutes `seed.sql` repetidamente** sobre una base ya poblada, porque puede provocar conflictos con campos únicos.

6. Inicia el servidor web desde la **raíz**, no desde `public`:

   ```bash
   php -S localhost:8000 -t public
   ```

7. Abre [http://localhost:8000](http://localhost:8000).

> El servidor integrado de PHP es para desarrollo y demostraciones locales; no debe exponerse como servidor de producción.

## Rutas principales

| Módulo | Ruta |
|---|---|
| Inicio | `/` |
| Servicios | `/servicios/index.php` |
| Crear servicio | `/servicios/crear.php` |
| Facturas | `/facturas/index.php` |
| Nueva factura | `/facturas/crear.php` |
| Reporte | `/reporte/index.php` |

Los detalles, la edición y la eliminación de servicios usan identificadores en la URL; la eliminación requiere confirmación mediante POST y token CSRF.

## Estructura del proyecto

```text
public/                  Punto de entrada web y páginas de módulos
  index.php              Panel principal
  servicios/             CRUD web de servicios
  facturas/              Creación y consulta de facturas
  reporte/               Reporte web
  css/estilos.css        Estilos globales propios
views/                   Layout, vistas y mensajes
  layout/                encabezado.php y pie.php
  partials/              Mensajes y avisos
src/                     Dominio, contratos, repositorios, servicios y validación
config/                  Ejemplo de configuración (config.example.php)
database/                schema.sql y seed.sql
bootstrap.php            Inicialización, sesión y conexión a BD
composer.json            Requisitos y autoload PSR-4
main.php                 Demostración original por consola
```

Composer asigna el namespace `App\\` a `src/` mediante PSR-4. Si cambias su configuración, ejecuta `composer dump-autoload`.

## Modelo relacional (resumen)

- `clientes` — personas a quienes se emiten facturas.
- `servicios` — catálogo de servicios, tipo, importe/valores específicos e imagen.
- `facturas` — cliente y período de facturación.
- `factura_detalles` — asociación entre factura y servicio, con descripción, cantidad, precio e importe guardados.

Relaciones: **cliente 1:N facturas**, **factura 1:N detalles** y **servicio 1:N detalles**. El esquema íntegro, restricciones e índices se encuentran en `database/schema.sql`.

## Principios de orientación a objetos

- **Abstracción:** `Facturable` y `Servicio` definen las operaciones comunes de facturación.
- **Encapsulamiento:** los modelos administran sus propios datos y validaciones.
- **Herencia:** `ServicioMedido`, `ServicioTarifaPlana` y `ServicioPorEvento` especializan `Servicio`.
- **Polimorfismo:** la factura obtiene descripciones e importes a través de las operaciones del contrato, sin necesitar decidir el subtipo en las vistas.

## Validaciones y controles

- Reglas de dominio como tarifas positivas, cantidades positivas y lecturas coherentes.
- Validaciones del formulario en servidor; restricciones HTML5 complementarias.
- Carga y tratamiento de imágenes mediante `GestorImagenes`.
- Protección **CSRF** en operaciones de escritura y patrón **POST/Redirect/GET**.
- Consultas preparadas para la persistencia y escape de valores dinámicos con `e()` al mostrarlos en HTML.

## Pruebas y resolución de problemas

- Revisa primero que PHP, `pdo_mysql`, Composer y MySQL/MariaDB estén disponibles.
- Si falta `vendor/autoload.php`, vuelve a ejecutar `composer install`.
- Si la conexión falla, confirma `config/config.php`, las credenciales y que exista `facturacion_servicios`.
- Si no hay registros de ejemplo, verifica que `database/seed.sql` se haya ejecutado después de `database/schema.sql`.
- Prueba manualmente: crear/editar/eliminar servicios, crear factura, consultar detalle y abrir el reporte.
- Para pruebas automatizadas, utiliza los scripts de `tests/` que existan en tu versión del repositorio y sigue sus instrucciones. No se declara una ejecución exitosa sin haberlas ejecutado.

## Capturas y evidencias (pendientes de incorporar)

En el **commit de evidencias** se agregarán capturas reales del sistema funcionando y sus enlaces aquí: Inicio, listado/creación/detalle/edición/eliminación de Servicios, listado/creación/detalle de Facturas y Reporte. No se incluyen imágenes de ejemplo simuladas.

## De Fase 1 a Fase 2

La Fase 1 implementaba el cálculo de servicios, la factura y su impresión en consola (`php main.php`). La Fase 2 mantiene ese dominio y agrega **base de datos relacional**, **repositorios**, **Factory**, **CRUD de servicios**, **facturas web**, **reporte web**, **interfaz consistente** y **validación de solicitudes**.

## Seguridad y alcance

El servidor `php -S` y los datos del `seed.sql` son solo para desarrollo. No publiques credenciales, no subas `config/config.php`, y no interpretes las comprobaciones de auditoría estática como garantía de seguridad completa.

## Diagramas y evidencias de Fase 2

- [Diagrama del modelo relacional](docs/modelo-relacional.md)
- [Diagrama de clases y correspondencia con tablas](docs/clases-y-tablas.md)
- [Guia de capturas reales de las paginas web](docs/evidencias/README.md)

Las capturas PNG deben agregarse a `docs/evidencias/` una vez verificadas las rutas en el navegador.