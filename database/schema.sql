CREATE DATABASE IF NOT EXISTS facturacion_servicios
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE facturacion_servicios;

CREATE TABLE clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(254) NOT NULL UNIQUE,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_clientes_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE servicios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    imagen VARCHAR(255) NULL,
    lectura_anterior DECIMAL(12,3) NULL,
    lectura_actual DECIMAL(12,3) NULL,
    tarifa_por_unidad DECIMAL(12,4) NULL,
    mensualidad DECIMAL(12,2) NULL,
    cantidad_eventos INT UNSIGNED NULL,
    tarifa_por_evento DECIMAL(12,2) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_servicios_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0),
    CONSTRAINT chk_servicios_tipo CHECK (tipo IN ('medido', 'tarifa_plana', 'por_evento')),
    CONSTRAINT chk_servicios_medidos CHECK (
      tipo <> 'medido' OR (
        lectura_anterior IS NOT NULL AND lectura_actual IS NOT NULL AND tarifa_por_unidad IS NOT NULL
        AND lectura_anterior >= 0 AND lectura_actual >= lectura_anterior AND tarifa_por_unidad > 0
        AND mensualidad IS NULL AND cantidad_eventos IS NULL AND tarifa_por_evento IS NULL
      )
    ),
    CONSTRAINT chk_servicios_planos CHECK (
      tipo <> 'tarifa_plana' OR (
        mensualidad IS NOT NULL AND mensualidad > 0 AND lectura_anterior IS NULL
        AND lectura_actual IS NULL AND tarifa_por_unidad IS NULL AND cantidad_eventos IS NULL AND tarifa_por_evento IS NULL
      )
    ),
    CONSTRAINT chk_servicios_eventos CHECK (
      tipo <> 'por_evento' OR (
        cantidad_eventos IS NOT NULL AND cantidad_eventos > 0 AND tarifa_por_evento IS NOT NULL AND tarifa_por_evento > 0
        AND lectura_anterior IS NULL AND lectura_actual IS NULL AND tarifa_por_unidad IS NULL AND mensualidad IS NULL
      )
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE facturas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id BIGINT UNSIGNED NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    creada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_facturas_cliente_periodo UNIQUE (cliente_id, fecha_inicio, fecha_fin),
    CONSTRAINT chk_facturas_periodo CHECK (fecha_fin > fecha_inicio),
    CONSTRAINT fk_facturas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE factura_detalles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED NOT NULL,
    servicio_id BIGINT UNSIGNED NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    precio_unitario DECIMAL(12,4) NOT NULL,
    importe DECIMAL(14,2) NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_detalles_cantidad CHECK (cantidad >= 0),
    CONSTRAINT chk_detalles_precio CHECK (precio_unitario > 0),
    CONSTRAINT chk_detalles_importe CHECK (importe >= 0),
    CONSTRAINT fk_detalles_factura FOREIGN KEY (factura_id) REFERENCES facturas(id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalles_servicio FOREIGN KEY (servicio_id) REFERENCES servicios(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
