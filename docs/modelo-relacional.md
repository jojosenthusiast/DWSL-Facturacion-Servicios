# Modelo relacional - Caso F, Fase 2

El diagrama representa las cuatro tablas de `database/schema.sql`. Los datos de factura se guardan en `factura_detalles` para conservar la descripcion, cantidad, precio y subtotal facturados.

```mermaid
erDiagram
  clientes ||--o{ facturas : "recibe"
  facturas ||--o{ factura_detalles : "contiene"
  servicios ||--o{ factura_detalles : "se incluye en"
  clientes {
    BIGINT id PK
    VARCHAR codigo UK
    VARCHAR nombre
    VARCHAR correo UK
    DATETIME creado_en
  }
  servicios {
    BIGINT id PK
    VARCHAR codigo UK
    VARCHAR nombre
    VARCHAR tipo
    BOOLEAN activo
    VARCHAR imagen
    DECIMAL lectura_anterior
    DECIMAL lectura_actual
    DECIMAL tarifa_por_unidad
    DECIMAL mensualidad
    INT cantidad_eventos
    DECIMAL tarifa_por_evento
    DATETIME creado_en
  }
  facturas {
    BIGINT id PK
    BIGINT cliente_id FK
    DATE fecha_inicio
    DATE fecha_fin
    DATETIME creada_en
  }
  factura_detalles {
    BIGINT id PK
    BIGINT factura_id FK
    BIGINT servicio_id FK
    VARCHAR descripcion
    DECIMAL cantidad
    DECIMAL precio_unitario
    DECIMAL importe
    DATETIME creado_en
  }
```

Las relaciones son de uno a muchos desde clientes a facturas, desde facturas a factura_detalles y desde servicios a factura_detalles. La unicidad cliente-periodo esta definida en el SQL.