<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\DatabaseException;
use App\Factories\ServicioFactory;
use App\Servicios\Servicio;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

final class ServicioRepositorio
{
    /** [INYECCION-DEPENDENCIAS] La conexión la proporciona el código que usa el repositorio. */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** [CRUD-CREATE] Devuelve la PK y la asigna al mismo objeto de dominio. */
    public function crear(Servicio $servicio): int
    {
        if ($servicio->getIdBd() !== null) {
            throw new InvalidArgumentException('El servicio ya tiene un ID de BD.');
        }

        $this->ejecutar(
            'INSERT INTO servicios
             (codigo, nombre, tipo, activo, imagen, lectura_anterior, lectura_actual,
              tarifa_por_unidad, mensualidad, cantidad_eventos, tarifa_por_evento)
             VALUES (:codigo, :nombre, :tipo, :activo, :imagen, :lectura_anterior, :lectura_actual,
                     :tarifa_por_unidad, :mensualidad, :cantidad_eventos, :tarifa_por_evento)',
            $servicio->datosPersistibles()
        );

        try {
            $id = (int) $this->pdo->lastInsertId();
        } catch (PDOException $exception) {
            throw new DatabaseException($exception);
        }
        $servicio->setIdBd($id);

        return $id;
    }

    /** [CRUD-READ] @return list<Servicio> Incluye inactivos salvo que se solicite lo contrario. */
    public function listar(bool $soloActivos = false): array
    {
        $consulta = $this->ejecutar(
            'SELECT * FROM servicios' . ($soloActivos ? ' WHERE activo = :activo' : '') . ' ORDER BY id DESC',
            $soloActivos ? ['activo' => 1] : []
        );

        return array_map(static fn (array $fila): Servicio => ServicioFactory::desdeFila($fila), $consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    /** [CRUD-READ] La Factory reconstruye el objeto; el repositorio no identifica subclases. */
    public function buscarPorId(int $id): ?Servicio
    {
        $this->validarId($id);
        $fila = $this->ejecutar('SELECT * FROM servicios WHERE id = :id', ['id' => $id])->fetch(PDO::FETCH_ASSOC);

        return $fila === false ? null : ServicioFactory::desdeFila($fila);
    }

    /** [CRUD-UPDATE] Los campos ajenos al subtipo quedan en NULL, incluso al cambiar el tipo. */
    public function actualizar(Servicio $servicio): bool
    {
        $id = $servicio->getIdBd();
        if ($id === null) {
            throw new InvalidArgumentException('El servicio necesita un ID de BD para actualizarse.');
        }

        $consulta = $this->ejecutar(
            'UPDATE servicios SET codigo = :codigo, nombre = :nombre, tipo = :tipo,
             activo = :activo, imagen = :imagen, lectura_anterior = :lectura_anterior,
             lectura_actual = :lectura_actual, tarifa_por_unidad = :tarifa_por_unidad,
             mensualidad = :mensualidad, cantidad_eventos = :cantidad_eventos,
             tarifa_por_evento = :tarifa_por_evento WHERE id = :id',
            $servicio->datosPersistibles() + ['id' => $id]
        );

        // MySQL puede devolver 0 cuando los valores no cambiaron.
        return $consulta->rowCount() > 0 || $this->buscarPorId($id) !== null;
    }

    /** [CRUD-DELETE] El borrado de imágenes lo coordina la capa web con GestorImagenes. */
    public function eliminar(int $id): bool
    {
        $this->validarId($id);

        return $this->ejecutar('DELETE FROM servicios WHERE id = :id', ['id' => $id])->rowCount() > 0;
    }

    /** [SEGURIDAD] Parámetros enlazados y errores PDO ocultos mediante la excepción común. */
    private function ejecutar(string $sql, array $datos = []): PDOStatement
    {
        try {
            $consulta = $this->pdo->prepare($sql);
            foreach ($datos as $campo => $valor) {
                $tipo = $valor === null ? PDO::PARAM_NULL : (is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
                $consulta->bindValue(':' . $campo, $valor, $tipo);
            }
            $consulta->execute();

            return $consulta;
        } catch (PDOException $exception) {
            throw new DatabaseException($exception);
        }
    }

    private function validarId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('El ID de BD debe ser mayor que cero.');
        }
    }
}
