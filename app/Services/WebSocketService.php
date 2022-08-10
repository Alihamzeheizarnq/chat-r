<?php

namespace App\Services;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * WebSocketService
 */
class WebSocketService implements WebSocketHandlerInterface {
    /**@var \Swoole\Table $wsTable */
    private $wsTable;

    public function __construct () {
        $this->wsTable = app('swoole')->wsTable;
    }

    // Scene：bind UserId & FD in WebSocket
    public function onOpen (Server $server, Request $request) {
        Log::info($request->fd . ' connected');

        $userId = null;
        if ( !$userId ) {
            // Disconnect the connections of unlogged users
            $server->disconnect($request->fd);

            return;
        }
        $this->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// Bind map uid to fd
        $this->wsTable->set('fd:' . $request->fd, ['value' => $userId]); // Bind map fd to uid
        $server->push($request->fd, json_encode([
            'event' => 'order.' . 1,
            [
                'ali'      => 'ali',
                'hamzehei' => 12
            ]
        ]));
    }

    public function onMessage (Server $server, Frame $frame) {
        Log::info(json_encode($frame->data));
        // Broadcast
        foreach ( $this->wsTable as $key => $row ) {

            Log::info($key . ' +' . json_encode());
            if ( str_starts_with($key, 'uid:') && $server->isEstablished($row['value']) ) {
                $content = sprintf('Broadcast: new message "%s" from #%d', $frame->data, $frame->fd);

                Log::info($content);
                $server->push($row['value'], $content);
            }
        }
    }

    public function onDisconnected (Server $server, Frame $frame) {
        // Broadcast
        foreach ( $this->wsTable as $key => $row ) {
            if ( str_starts_with($key, 'uid:') && $server->isEstablished($row['value']) ) {
                $content = sprintf('Broadcast: diss new message "%s" from #%d', $frame->data, $frame->fd);

                Log::info($content);
                $server->push($row['value'], $content);
            }
        }
    }

    /**
     * The function is executed when a new WebSocket connection is closed.
     *
     * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-close
     *
     * @param Server $server
     * @param        $fd
     * @param        $reactorId
     *
     * @return void
     */
    public function onClose (Server $server, $fd, $reactorId) : void {
        Log::info('disconnectd =>' . $fd);
        $uid = $this->wsTable->get('fd:' . $fd);
        if ( $uid !== false ) {
            $this->wsTable->del('uid:' . $uid['value']); // Unbind uid map
        }
        $this->wsTable->del('fd:' . $fd);// Unbind fd map
        $server->push($fd, "Goodbye #{$fd}");
    }
}
