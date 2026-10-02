<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$fixture = __DIR__ . '/fixtures/uncaught_exception.php';
$process = proc_open(
    [PHP_BINARY, '-d', 'display_errors=1', $fixture],
    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes
);
if (!is_resource($process)) {
    throw new RuntimeException('Could not start the exception-handler regression process.');
}
fclose($pipes[0]);
$response = stream_get_contents($pipes[1]);
$log = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

$expected = \App\Exceptions\DatabaseException::PUBLIC_MESSAGE;
if ($exitCode === 0 || trim($response) !== $expected
    || str_contains($response, 'private-sentinel')
    || str_contains($response, 'SQLSTATE')
    || str_contains($response, 'Fatal error')) {
    throw new RuntimeException('Error-handler test output: ' . json_encode(['exit' => $exitCode, 'response' => $response, 'log' => $log]));
}
