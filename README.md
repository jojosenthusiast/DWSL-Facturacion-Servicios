# Facturacion de Servicios (Caso F)

Sistema de facturacion de servicios desarrollado en PHP aplicando diseno orientado a objetos, como parte de la asignatura **Desarrollo Web con Software Libre**.

## Caso de estudio

Una empresa de servicios factura consumos de distinta naturaleza: servicios medidos (agua, energia), servicios de tarifa plana (mensualidad fija) y servicios por evento (por cantidad). El sistema emite una factura mensual sumando servicios heterogeneos mediante polimorfismo puro, sin condicionales por tipo concreto.

## Integrantes

- Coto Beltran, Angel Eduardo
- Fernando Emilio Valle Bernal
- Jorge Alexis Ramos Ramos
- Murgas Juarez, Carlos Gabriel
- Munguia Noyola, Leonel Alexander
- Milton Josue Ramirez Gongora

## Estructura del proyecto

```
src/
  Contratos/
    Facturable.php         Contrato con calcularImporte() y obtenerDescripcion()
  Servicios/
    Servicio.php           Clase abstracta base (id, nombre, activo, validaciones)
  ServicioMedido.php        Servicio con lectura anterior/actual x tarifa
  ServicioTarifaPlana.php   Servicio de mensualidad fija
  ServicioPorEvento.php     Servicio cobrado por cantidad de eventos
  Cliente.php               Titular de la factura, valida el correo
  PeriodoFacturacion.php    Rango de fechas inmutable del periodo facturado
  Factura.php               Coordina servicios (Facturable) y calcula el total
main.php                   Punto de entrada: arma los objetos y ejecuta el reporte
```

## Requisitos

- PHP 8.1 o superior
- Composer

## Como ejecutarlo

```bash
composer install
php main.php
```

## Diseno orientado a objetos

- **Abstraccion**: la interfaz `Facturable` y la clase abstracta `Servicio` exponen que hace un objeto cobrable sin revelar como calcula su importe.
- **Encapsulamiento**: todos los atributos son `private`; los setters validan el estado (lecturas coherentes, tarifas no negativas, correo valido, fechas inmutables).
- **Herencia**: `ServicioMedido`, `ServicioTarifaPlana` y `ServicioPorEvento` heredan de `Servicio` porque genuinamente son un `Servicio`.
- **Polimorfismo**: `Factura::calcularTotal()` recorre la coleccion de `Facturable` y llama `calcularImporte()` sobre cada elemento sin usar `instanceof`.
