<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class DatabaseException extends RuntimeException
{
    public const PUBLIC_MESSAGE = 'No fue posible completar la operación. Inténtalo más tarde.';

    public function __construct(Throwable $previous)
    {
        parent::__construct(self::PUBLIC_MESSAGE, 0, $previous);
    }
}
