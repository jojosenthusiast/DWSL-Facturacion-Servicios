<?php

declare(strict_types=1);

namespace App;

class Cliente
{
    private string $id;
    private string $nombre;
    private string $correo;

    public function __construct(string $id, string $nombre, string $correo)
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException('El id del cliente no puede estar vacio.');
        }

        if (trim($nombre) === '') {
            throw new \InvalidArgumentException('El nombre del cliente no puede estar vacio.');
        }

        $this->id = $id;
        $this->nombre = $nombre;
        $this->setCorreo($correo);
    }

    public function setCorreo(string $correo): void
    {
        if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException(sprintf('El correo "%s" no es valido.', $correo));
        }

        $this->correo = $correo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }
}
