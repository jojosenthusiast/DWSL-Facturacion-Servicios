# Clases y persistencia - Caso F

Diagrama de estructura conceptual. Las clases de repositorio se encargan de las tablas; el dominio se mantiene desacoplado de la presentacion web.

```mermaid
classDiagram
  class Facturable {
    <<interface>>
    +calcularImporte()
    +obtenerDescripcion()
  }
  class Servicio {
    <<abstract>>
  }
  class ServicioMedido
  class ServicioTarifaPlana
  class ServicioPorEvento
  class Factura
  class Cliente
  class PeriodoFacturacion
  class ServicioRepositorio
  class FacturaRepositorio
  class ServicioFactory
  Facturable <|.. Servicio
  Servicio <|-- ServicioMedido
  Servicio <|-- ServicioTarifaPlana
  Servicio <|-- ServicioPorEvento
  Factura --> Cliente
  Factura --> PeriodoFacturacion
  Factura o-- Facturable
  ServicioRepositorio ..> Servicio
  ServicioFactory ..> Servicio
  FacturaRepositorio ..> Factura
```

| Clase | Tabla o responsabilidad |
| --- | --- |
| Cliente | clientes |
| Servicio y subclases | servicios, diferenciados por tipo |
| Factura | facturas y factura_detalles |
| PeriodoFacturacion | fecha_inicio y fecha_fin de facturas |
| ServicioRepositorio | lectura/escritura de servicios |
| FacturaRepositorio | lectura/escritura de facturas, detalles y clientes |
| ServicioFactory | reconstruccion polimorfica; no es una tabla |