# Modelos, Factory y repositorio de Servicios

Rama: `feature/servicios-repository-factory`.

Esta parte añade persistencia a los modelos de Fase 1 y utiliza la estructura,
conexión, validaciones, excepción y esquema que ya integró Carlos. Los namespaces,
constructores, cálculos y validaciones existentes se conservan. El autoload actual
de Composer (`App\\` → `src/`) también sirve para las carpetas nuevas.

## API de los modelos

| Método | Uso |
| --- | --- |
| `getId()` / `getCodigo()` | Código de texto de Fase 1, por ejemplo `SRV-AGUA`. |
| `getIdBd()` / `setIdBd(int)` | PK numérica de `servicios`. Antes de guardar es `null`. |
| `getImagen()` / `setImagen(?string)` | Nombre de imagen o `null`; no admite rutas. |
| `estaActivo()` / `setActivo(bool)` | Estado del servicio. |
| `getTipo()` | Valor compatible con la columna `tipo` de Carlos. |
| `tipoLegible()` | Etiqueta para listado, detalle y formularios. |
| `datosEspecificos()` | Valores del subtipo, usando nombres de columnas. |
| `camposEspecificos()` | Etiqueta, tipo HTML, mínimo, máximo, paso y obligatoriedad de cada campo. |
| `datosPersistibles()` | Datos comunes y específicos; columnas de otros subtipos en `null`. |
| `calcularImporte()` / `obtenerDescripcion()` | Contrato `Facturable` original. |

Las tarifas y la cantidad de eventos siguen siendo mayores que cero. Las lecturas
pueden ser cero y la actual no puede ser menor que la anterior.

## Factory

```php
use App\Factories\ServicioFactory;

$opciones = ServicioFactory::tiposDisponibles();
$camposPorTipo = ServicioFactory::camposPorTipo();
$servicio = ServicioFactory::desdeFormulario([
    'codigo' => 'SRV-NUEVO',
    'nombre' => 'Visita técnica',
    'tipo' => 'evento',
    'cantidad_eventos' => '2',
    'tarifa_por_evento' => '15.00',
    'activo' => '1',
]);
```

La Factory selecciona las clases concretas y reconstruye filas con `desdeFila()`.
Acepta `medido`, `tarifa_plana`, `evento` y el alias `por_evento`. El servicio por
evento se persiste como `por_evento`, tal como exigen `schema.sql` y `seed.sql`.
No se modifica el esquema para cambiar este valor.

Los valores numéricos del formulario se validan antes de convertirlos; no se
truncan cantidades fraccionarias ni se convierten textos inválidos en cero.
Un checkbox `activo` ausente significa desmarcado. El ID enviado por formulario
se ignora. Las entradas inválidas lanzan `InvalidArgumentException`.

El nombre de imagen debe ser el resultado confiable de `GestorImagenes`,
proporcionado por la capa web, y no un nombre aceptado directamente de `$_POST`.

## Repositorio

```php
use App\Repositories\ServicioRepositorio;

// PDO lo proporciona Conexion o conexion_obtener() desde bootstrap.php.
$repositorio = new ServicioRepositorio($pdo);
$id = $repositorio->crear($servicio); // También asigna getIdBd() al objeto.
$guardado = $repositorio->buscarPorId($id); // Servicio o null.
$todos = $repositorio->listar();
$activos = $repositorio->listar(true);

if ($guardado !== null) {
    $guardado->setActivo(false);
    $repositorio->actualizar($guardado);
}
$eliminado = $repositorio->eliminar($id);
```

Para actualizar datos de un formulario se construye otro objeto con
`desdeFormulario()` y se asigna la PK validada de la ruta con `setIdBd($id)`.
El repositorio limpia las columnas del subtipo anterior si cambia el tipo.
`actualizar()` devuelve `true` si el registro existe, incluso sin cambios;
`eliminar()` devuelve `false` si ya no existe.

Todas las consultas con datos usan prepared statements. Las excepciones PDO se
envuelven en la `DatabaseException` existente, que ofrece un mensaje público
genérico y conserva la causa para diagnóstico interno. Se respeta la FK del
esquema: un servicio asociado a detalles de factura no se puede eliminar.

El repositorio guarda nombres de imágenes. La subida, reemplazo y eliminación de
archivos corresponden a `GestorImagenes` y a la coordinación del CRUD web.

## Vistas polimórficas

```php
echo e($servicio->tipoLegible());
echo e($servicio->getNombre());
foreach ($servicio->datosEspecificos() as $campo => $valor) {
    $definicion = $servicio->camposEspecificos()[$campo];
    echo e($definicion['etiqueta']) . ': ' . e($valor);
}
echo e(number_format($servicio->calcularImporte(), 2));
```

Para creación se obtienen opciones y campos desde la Factory; para edición y
detalle se obtienen directamente del modelo. La selección de subclases ocurre
en la Factory. Las vistas recorren metadatos sin `instanceof`, `switch`, `match`
ni comparaciones por tipo.

## Comprobaciones

Después de `composer install`:

```bash
php main.php
php tests/run.php
php tests/domain.php
php tests/servicio_factory.php
```

Reconstruir una BD local de pruebas con los SQL existentes de Carlos. Definir
`DWSL_TEST_DSN`, `DWSL_TEST_USER` y `DWSL_TEST_PASSWORD`, igual que en sus pruebas.
Por ejemplo, el DSN puede ser
`mysql:host=127.0.0.1;dbname=facturacion_servicios;charset=utf8mb4`.

```bash
php tests/connection.php
php tests/database.php
php tests/servicio_repositorio.php
```

La prueba del repositorio comprueba los tres subtipos, PK, imágenes, estado,
actualización sin cambios, cambio de subtipo, columnas nulas, protección contra
inyección SQL, código duplicado y restricción FK. Revierte su transacción para
conservar los registros; el contador AUTO_INCREMENT puede avanzar al probar.
