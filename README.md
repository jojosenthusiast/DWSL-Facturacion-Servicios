# Facturación de Servicios (Caso F)

Sistema de facturación de servicios desarrollado en PHP aplicando diseño orientado a objetos, como parte de la asignatura **Desarrollo Web con Software Libre**.

## Caso de estudio

Una empresa de servicios factura consumos de distinta naturaleza: servicios medidos como agua y energía, servicios de tarifa plana con mensualidad fija y servicios por evento cobrados según cantidad.

El sistema permite generar una factura mensual agrupando servicios de diferentes tipos mediante polimorfismo, manteniendo separado el cálculo de los servicios de la presentación final de la factura.

## Integrantes

* Coto Beltran, Angel Eduardo
* Fernando Emilio Valle Bernal
* Jorge Alexis Ramos Ramos
* Murgas Juarez, Carlos Gabriel
* Munguia Noyola, Leonel Alexander
* Milton Josue Ramirez Gongora

## Descripción del proyecto

El proyecto implementa un sistema de facturación capaz de registrar un cliente, definir un período de facturación y agregar distintos tipos de servicios.

Cada servicio calcula su importe según sus propias reglas y la clase `Factura` se encarga de reunir los servicios y calcular el total.

La presentación de la factura se mantiene separada del cálculo mediante el módulo `ReporteFacturaConsola`, encargado de mostrar de forma legible en consola:

* cliente;
* período de facturación;
* servicios incluidos;
* descripción de cada servicio;
* importe de cada servicio;
* total de la factura.

## Estructura del proyecto

```text
src/
├── Contratos/
│   └── Facturable.php
│       Contrato con calcularImporte() y obtenerDescripcion()
│
├── Reportes/
│   └── ReporteFacturaConsola.php
│       Genera la salida legible de la factura en consola
│
├── Servicios/
│   └── Servicio.php
│       Clase abstracta base para los servicios
│
├── ServicioMedido.php
│   Servicio calculado mediante lectura anterior, lectura actual y tarifa
│
├── ServicioTarifaPlana.php
│   Servicio con mensualidad fija
│
├── ServicioPorEvento.php
│   Servicio cobrado según cantidad de eventos
│
├── Cliente.php
│   Representa al titular de la factura y valida su información
│
├── PeriodoFacturacion.php
│   Representa el rango de fechas del período facturado
│
└── Factura.php
    Coordina los objetos Facturable y calcula el total de la factura

composer.json
    Configuración del proyecto y autoload mediante Composer

composer.lock
    Registro de las versiones utilizadas por Composer

main.php
    Punto de entrada del programa

README.md
    Documentación general del proyecto
```

## Requisitos

Para ejecutar el proyecto se necesita:

* PHP 8.1 o superior
* Composer

Para comprobar la versión instalada de PHP:

```bash
php -v
```

Para comprobar la instalación de Composer:

```bash
composer --version
```

## Instalación

Después de clonar o descargar el repositorio, abrir una terminal en la carpeta raíz del proyecto.

Instalar las dependencias y generar los archivos necesarios de Composer:

```bash
composer install
```

Si se realizan cambios en la estructura de clases o en la configuración de autoload, se puede regenerar manualmente utilizando:

```bash
composer dump-autoload
```

## Ejecución

Desde la carpeta raíz del proyecto ejecutar:

```bash
php main.php
```

El programa mostrará una factura directamente en la consola.

La salida contiene una estructura similar a la siguiente:

```text
============================================================
FACTURA MENSUAL
============================================================
Cliente: Ana Martinez
Periodo: julio 2026
------------------------------------------------------------

Servicio 1
Descripcion: Agua potable (medido) - consumo: ...
Importe: $21.68

Servicio 2
Descripcion: Energia electrica (medido) - consumo: ...
Importe: $15.40

Servicio 3
Descripcion: Internet residencial (tarifa plana) - mensualidad: $35.00
Importe: $35.00

Servicio 4
Descripcion: Mantenimiento de areas comunes (por evento) - ...
Importe: $30.00

------------------------------------------------------------
Total: $102.08
============================================================
```

Los importes dependen de los datos proporcionados a los servicios desde `main.php`.

## Uso de Composer

Composer se utiliza para administrar la configuración y carga automática de las clases del proyecto.

El archivo `composer.json` utiliza el estándar PSR-4 para asociar el namespace:

```text
App\
```

con la carpeta:

```text
src/
```

Esto permite utilizar las clases del proyecto sin tener que incluir manualmente cada archivo PHP mediante múltiples instrucciones `require`.

Después de modificar la configuración del autoload se puede ejecutar:

```bash
composer dump-autoload
```

para regenerar el sistema de carga automática.

## ¿Por qué no se guarda `/vendor/` en Git?

La carpeta `vendor/` es generada automáticamente por Composer.

Esta carpeta contiene archivos de dependencias y el sistema de autoload, por lo que no es necesario almacenarla directamente en el repositorio.

Después de clonar el proyecto, cualquier integrante puede reconstruirla ejecutando:

```bash
composer install
```

Por esta razón, `/vendor/` debe mantenerse dentro del archivo `.gitignore`.

## Diseño orientado a objetos

* **Abstracción:** la interfaz `Facturable` y la clase abstracta `Servicio` definen qué debe hacer un objeto cobrable sin depender de la forma específica en que calcula su importe.

* **Encapsulamiento:** los atributos se mantienen protegidos dentro de las clases y se aplican validaciones para garantizar estados correctos, como lecturas coherentes, tarifas válidas, correos válidos y períodos de facturación correctos.

* **Herencia:** `ServicioMedido`, `ServicioTarifaPlana` y `ServicioPorEvento` heredan de `Servicio`, ya que representan diferentes tipos de servicios.

* **Polimorfismo:** `Factura::calcularTotal()` recorre una colección de objetos que implementan `Facturable` y llama a `calcularImporte()` sin necesitar verificar el tipo concreto mediante `instanceof`.

## Separación de responsabilidades

El proyecto mantiene separado el cálculo de la factura de su presentación.

La clase `Factura` se encarga de administrar los servicios y obtener los importes y el total.

El módulo:

```text
src/Reportes/ReporteFacturaConsola.php
```

se encarga exclusivamente de recibir la factura y generar una representación legible para mostrarla en consola.

Esta separación permite modificar el formato de presentación sin alterar las reglas utilizadas para calcular los importes de los servicios.
