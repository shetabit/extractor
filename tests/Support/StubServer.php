<?php

namespace Shetabit\Extractor\Tests\Support;

use RuntimeException;

/**
 * PHP's built in web server, started for the length of the test suite.
 *
 * The feature tests send their requests to it, so that the package is exercised
 * over a real connection instead of a replaced handler.
 */
final class StubServer
{
    private static self|null $instance = null;

    /**
     * @param resource $process
     */
    private function __construct(private $process, private readonly string $host, private readonly int $port)
    {
    }

    /**
     * The server of this test run, started the first time it is asked for.
     */
    public static function instance() : self
    {
        return self::$instance ??= self::start();
    }

    /**
     * The url of the given path on the stub server.
     */
    public function url(string $path = '/') : string
    {
        return 'http://'.$this->host.':'.$this->port.'/'.ltrim($path, '/');
    }

    /**
     * A port nothing listens on, for the tests of a failing request.
     */
    public function unusedPort() : int
    {
        return $this->port + 1;
    }

    /**
     * Stop the server.
     */
    public function stop() : void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }

        self::$instance = null;
    }

    /**
     * Start the server on a free port and wait for it to answer.
     */
    private static function start() : self
    {
        $host = '127.0.0.1';
        $port = self::freePort($host);

        $process = proc_open(
            [PHP_BINARY, '-S', $host.':'.$port, '-t', __DIR__.'/server', __DIR__.'/server/router.php'],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            throw new RuntimeException('The stub server could not be started.');
        }

        $server = new self($process, $host, $port);
        $server->waitUntilItAnswers();

        register_shutdown_function(static fn () => $server->stop());

        return $server;
    }

    /**
     * A port that is free at the moment it is asked for.
     */
    private static function freePort(string $host) : int
    {
        $socket = stream_socket_server('tcp://'.$host.':0', $code, $message);

        if ($socket === false) {
            throw new RuntimeException("No port could be reserved for the stub server: {$message}");
        }

        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    /**
     * Give the server the moment it needs to accept connections.
     */
    private function waitUntilItAnswers(int $tries = 100) : void
    {
        for ($try = 0; $try < $tries; $try++) {
            $connection = @fsockopen($this->host, $this->port, $code, $message, 0.1);

            if (is_resource($connection)) {
                fclose($connection);

                return;
            }

            usleep(50_000);
        }

        $this->stop();

        throw new RuntimeException('The stub server did not start listening.');
    }
}
