<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Cliente;
use App\Factories\ServicioFactory;
use App\Factura;
use App\PeriodoFacturacion;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use PDOException;

final class FacturaRepositorio
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listarClientes(): array
    {
        $filas = $this->pdo
            ->query('SELECT id, codigo, nombre FROM clientes ORDER BY nombre')
            ->fetchAll();

        return array_map(
            static fn (array $fila): array => [
                'id' => (int) $fila['id'],
                'codigo' => (string) $fila['codigo'],
                'nombre' => (string) $fila['nombre'],
            ],
            $filas
        );
    }

    public function listarServiciosActivos(): array
    {
        $filas = $this->pdo
            ->query('SELECT id, codigo, nombre, tipo FROM servicios WHERE activo = TRUE ORDER BY tipo, nombre')
            ->fetchAll();

        return array_map(
            static fn (array $fila): array => [
                'id' => (int) $fila['id'],
                'codigo' => (string) $fila['codigo'],
                'nombre' => (string) $fila['nombre'],
                'tipo' => (string) $fila['tipo'],
            ],
            $filas
        );
    }

    public function existePeriodo(int $clienteId, DateTimeImmutable $inicio, DateTimeImmutable $fin): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM facturas
             WHERE cliente_id = :cliente_id AND fecha_inicio = :fecha_inicio AND fecha_fin = :fecha_fin'
        );
        $statement->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $statement->bindValue(':fecha_inicio', $inicio->format('Y-m-d'));
        $statement->bindValue(':fecha_fin', $fin->format('Y-m-d'));
        $statement->execute();

        return (int) $statement->fetchColumn() > 0;
    }

    public function crear(int $clienteId, PeriodoFacturacion $periodo, array $idsServicios): int
    {
        $idsServicios = array_values(array_unique(array_map('intval', $idsServicios)));
        if ($idsServicios === []) {
            throw new InvalidArgumentException('Selecciona al menos un servicio para la factura.');
        }

        $cliente = $this->obtenerFilaCliente($clienteId);
        if ($cliente === null) {
            throw new InvalidArgumentException('El cliente seleccionado no existe.');
        }

        $servicios = $this->obtenerFilasServicios($idsServicios);
        if (count($servicios) !== count($idsServicios)) {
            throw new InvalidArgumentException('Algunos servicios seleccionados no existen o no están activos.');
        }

        $transaccionPropia = !$this->pdo->inTransaction();
        if ($transaccionPropia) {
            $this->pdo->beginTransaction();
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO facturas (cliente_id, fecha_inicio, fecha_fin)
                 VALUES (:cliente_id, :fecha_inicio, :fecha_fin)'
            );
            $statement->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $statement->bindValue(':fecha_inicio', $periodo->getInicio()->format('Y-m-d'));
            $statement->bindValue(':fecha_fin', $periodo->getFin()->format('Y-m-d'));
            $statement->execute();

            $facturaId = (int) $this->pdo->lastInsertId();

            $detalle = $this->pdo->prepare(
                'INSERT INTO factura_detalles (factura_id, servicio_id, descripcion, cantidad, precio_unitario, importe)
                 VALUES (:factura_id, :servicio_id, :descripcion, :cantidad, :precio_unitario, :importe)'
            );

            foreach ($servicios as $fila) {
                $servicio = ServicioFactory::desdeFila($fila);
                [$cantidad, $precioUnitario] = $this->extraerCantidadYPrecio($fila);

                $detalle->bindValue(':factura_id', $facturaId, PDO::PARAM_INT);
                $detalle->bindValue(':servicio_id', (int) $fila['id'], PDO::PARAM_INT);
                $detalle->bindValue(':descripcion', $servicio->obtenerDescripcion());
                $detalle->bindValue(':cantidad', sprintf('%.3f', $cantidad));
                $detalle->bindValue(':precio_unitario', sprintf('%.4f', $precioUnitario));
                $detalle->bindValue(':importe', sprintf('%.2f', $servicio->calcularImporte()));
                $detalle->execute();
            }

            if ($transaccionPropia) {
                $this->pdo->commit();
            }

            return $facturaId;
        } catch (PDOException $exception) {
            if ($transaccionPropia && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($exception->getCode() === '23000') {
                throw new InvalidArgumentException('Ya existe una factura para ese cliente en ese período.');
            }

            throw $exception;
        }
    }

    public function listar(): array
    {
        return array_map(
            static function (array $entrada): array {
                $factura = $entrada['factura'];
                $periodo = $factura->obtenerPeriodo();

                return [
                    'id' => $entrada['id'],
                    'cliente' => $factura->obtenerCliente()->getNombre(),
                    'fecha_inicio' => $periodo->getInicio()->format('Y-m-d'),
                    'fecha_fin' => $periodo->getFin()->format('Y-m-d'),
                    'total' => $factura->calcularTotal(),
                ];
            },
            $this->buscarTodas()
        );
    }

    public function buscarPorId(int $id): ?Factura
    {
        $statement = $this->pdo->prepare(
            'SELECT f.id, f.fecha_inicio, f.fecha_fin, c.codigo, c.nombre, c.correo
             FROM facturas f
             JOIN clientes c ON c.id = f.cliente_id
             WHERE f.id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $fila = $statement->fetch();
        if ($fila === false) {
            return null;
        }

        return $this->construirFactura($fila, $this->obtenerDetalles($id));
    }

    public function obtenerDetalles(int $facturaId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT d.id, d.servicio_id, d.descripcion, d.cantidad, d.precio_unitario, d.importe,
                    s.codigo, s.nombre, s.tipo, s.lectura_anterior, s.lectura_actual,
                    s.tarifa_por_unidad, s.mensualidad, s.cantidad_eventos, s.tarifa_por_evento
             FROM factura_detalles d
             JOIN servicios s ON s.id = d.servicio_id
             WHERE d.factura_id = :factura_id
             ORDER BY d.id'
        );
        $statement->bindValue(':factura_id', $facturaId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function buscarTodas(): array
    {
        $filasFacturas = $this->pdo
            ->query(
                'SELECT f.id, f.fecha_inicio, f.fecha_fin, c.codigo, c.nombre, c.correo
                 FROM facturas f
                 JOIN clientes c ON c.id = f.cliente_id
                 ORDER BY f.id DESC'
            )
            ->fetchAll();

        if ($filasFacturas === []) {
            return [];
        }

        $filasDetalles = $this->pdo
            ->query(
                'SELECT d.factura_id, d.id, d.servicio_id, s.codigo, s.nombre, s.tipo,
                        s.lectura_anterior, s.lectura_actual, s.tarifa_por_unidad,
                        s.mensualidad, s.cantidad_eventos, s.tarifa_por_evento
                 FROM factura_detalles d
                 JOIN servicios s ON s.id = d.servicio_id
                 ORDER BY d.factura_id, d.id'
            )
            ->fetchAll();

        $detallesPorFactura = [];
        foreach ($filasDetalles as $filaDetalle) {
            $detallesPorFactura[(int) $filaDetalle['factura_id']][] = $filaDetalle;
        }

        $resultado = [];
        foreach ($filasFacturas as $filaFactura) {
            $id = (int) $filaFactura['id'];
            $resultado[] = [
                'id' => $id,
                'factura' => $this->construirFactura($filaFactura, $detallesPorFactura[$id] ?? []),
            ];
        }

        return $resultado;
    }

    private function construirFactura(array $filaFactura, array $filasDetalles): Factura
    {
        $cliente = new Cliente(
            (string) $filaFactura['codigo'],
            (string) $filaFactura['nombre'],
            (string) $filaFactura['correo']
        );

        $periodo = new PeriodoFacturacion(
            new DateTimeImmutable((string) $filaFactura['fecha_inicio']),
            new DateTimeImmutable((string) $filaFactura['fecha_fin'])
        );

        $factura = new Factura($cliente, $periodo);

        foreach ($filasDetalles as $filaDetalle) {
            $factura->agregarFacturable(ServicioFactory::desdeFila($this->filaServicio($filaDetalle)));
        }

        return $factura;
    }

    private function filaServicio(array $filaDetalle): array
    {
        $filaDetalle['id'] = (int) $filaDetalle['servicio_id'];

        return $filaDetalle;
    }

    private function extraerCantidadYPrecio(array $fila): array
    {
        return match ((string) $fila['tipo']) {
            'medido' => [
                (float) $fila['lectura_actual'] - (float) $fila['lectura_anterior'],
                (float) $fila['tarifa_por_unidad'],
            ],
            'tarifa_plana' => [1.0, (float) $fila['mensualidad']],
            'por_evento' => [(float) $fila['cantidad_eventos'], (float) $fila['tarifa_por_evento']],
            default => throw new InvalidArgumentException(
                sprintf('El tipo de servicio "%s" no es válido.', (string) $fila['tipo'])
            ),
        };
    }

    private function obtenerFilaCliente(int $clienteId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, codigo, nombre, correo FROM clientes WHERE id = :id');
        $statement->bindValue(':id', $clienteId, PDO::PARAM_INT);
        $statement->execute();

        $fila = $statement->fetch();

        return $fila === false ? null : $fila;
    }

    private function obtenerFilasServicios(array $idsServicios): array
    {
        $marcadores = [];
        foreach (array_keys($idsServicios) as $indice) {
            $marcadores[] = ':servicio_' . $indice;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, codigo, nombre, tipo, lectura_anterior, lectura_actual, tarifa_por_unidad,
                    mensualidad, cantidad_eventos, tarifa_por_evento
             FROM servicios
             WHERE activo = TRUE AND id IN (' . implode(', ', $marcadores) . ')'
        );

        foreach ($idsServicios as $indice => $idServicio) {
            $statement->bindValue(':servicio_' . $indice, $idServicio, PDO::PARAM_INT);
        }
        $statement->execute();

        $porId = array_column($statement->fetchAll(), null, 'id');

        $ordenadas = [];
        foreach ($idsServicios as $idServicio) {
            if (isset($porId[$idServicio])) {
                $ordenadas[] = $porId[$idServicio];
            }
        }

        return $ordenadas;
    }
}
