<?php

declare(strict_types=1);

ini_set('display_errors', '1');
require dirname(__DIR__, 2) . '/bootstrap.php';

throw new \App\Exceptions\DatabaseException(new \PDOException('SQLSTATE[HY000] private-sentinel'));
