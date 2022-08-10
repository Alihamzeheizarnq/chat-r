<?php

namespace App\Services;

use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;
use Illuminate\Support\Facades\Log;
use Swoole\Server;

/**
 * TestTcpSocket
 */
class TestTcpSocket extends TcpSocket {
    /**
     * @param \Swoole\Server $server
     * @param                $fd
     * @param                $reactorId
     *
     * @return void
     */
    public function onConnect (Server $server, $fd, $reactorId) {
        //        \Log::info('New TCP connection', [$fd]);
        $server->send($fd, 'Welcome to LaravelS.');
    }

    /**
     * @param \Swoole\Server $server
     * @param                $fd
     * @param                $reactorId
     * @param                $data
     *
     * @return void
     */
    public function onReceive (Server $server, $fd, $reactorId, $data) : void {
        Log::info('Received data', [$fd, $data]);
        $server->send($fd, 'LaravelS: ' . $data);
        if ( $data === "quit\r\n" ) {
            $server->send($fd, 'LaravelS: bye' . PHP_EOL);
            $server->close($fd);
        }
    }

    /**
     * @param \Swoole\Server $server
     * @param                $fd
     * @param                $reactorId
     *
     * @return void
     */
    public function onClose (Server $server, $fd, $reactorId) : void {
        Log::info('Close TCP connection', [$fd]);
        $server->send($fd, 'Goodbye');
    }
}
