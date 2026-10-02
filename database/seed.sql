USE facturacion_servicios;

INSERT INTO clientes (codigo, nombre, correo) VALUES
('CLI-001', 'Ana Martínez', 'ana.martinez@example.test'),
('CLI-002', 'Luis Hernández', 'luis.hernandez@example.test'),
('CLI-003', 'María López', 'maria.lopez@example.test');

INSERT INTO servicios
(codigo, nombre, tipo, activo, lectura_anterior, lectura_actual, tarifa_por_unidad, mensualidad, cantidad_eventos, tarifa_por_evento)
VALUES
('SRV-AGUA', 'Agua potable', 'medido', TRUE, 0.000, 12.500, 1.2500, NULL, NULL, NULL),
('SRV-ENERGIA', 'Energía eléctrica', 'medido', TRUE, 100.000, 180.000, 0.1925, NULL, NULL, NULL),
('SRV-GAS', 'Gas natural', 'medido', TRUE, 25.000, 31.000, 2.1000, NULL, NULL, NULL),
('SRV-INTERNET', 'Internet residencial', 'tarifa_plana', TRUE, NULL, NULL, NULL, 35.00, NULL, NULL),
('SRV-CABLE', 'Televisión por cable', 'tarifa_plana', TRUE, NULL, NULL, NULL, 22.50, NULL, NULL),
('SRV-MANTENIMIENTO', 'Mantenimiento común', 'tarifa_plana', TRUE, NULL, NULL, NULL, 15.00, NULL, NULL),
('SRV-RECOLECCION', 'Recolección extraordinaria', 'por_evento', TRUE, NULL, NULL, NULL, NULL, 2, 12.00),
('SRV-REPARACION', 'Visita de reparación', 'por_evento', TRUE, NULL, NULL, NULL, NULL, 1, 25.00),
('SRV-LIMPIEZA', 'Limpieza especial', 'por_evento', TRUE, NULL, NULL, NULL, NULL, 3, 8.00);

INSERT INTO facturas (cliente_id, fecha_inicio, fecha_fin) VALUES
(1, '2026-01-01', '2026-02-01'),
(2, '2026-01-01', '2026-02-01'),
(3, '2026-01-01', '2026-02-01');

INSERT INTO factura_detalles
(factura_id, servicio_id, descripcion, cantidad, precio_unitario, importe)
SELECT f.id, s.id, s.nombre,
  CASE s.tipo WHEN 'medido' THEN s.lectura_actual - s.lectura_anterior
    WHEN 'tarifa_plana' THEN 1 ELSE s.cantidad_eventos END,
  CASE s.tipo WHEN 'medido' THEN s.tarifa_por_unidad
    WHEN 'tarifa_plana' THEN s.mensualidad ELSE s.tarifa_por_evento END,
  CASE s.tipo WHEN 'medido' THEN (s.lectura_actual - s.lectura_anterior) * s.tarifa_por_unidad
    WHEN 'tarifa_plana' THEN s.mensualidad ELSE s.cantidad_eventos * s.tarifa_por_evento END
FROM facturas f
JOIN servicios s ON s.codigo IN ('SRV-AGUA', 'SRV-INTERNET', 'SRV-RECOLECCION')
WHERE f.cliente_id = 1;
