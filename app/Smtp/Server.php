<?php

/**
 * this file for smtp connnect event.
 *
 * @author wuchuheng <root@wuchuheng.com>
 *
 */

namespace App\Smtp;

use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\JsonRpc\TcpServer;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Server as SwooleServer;

class Server extends TcpServer
{

    public function onConnect(SwooleServer $Server)
    {
        //$Server->send($fd, ServerResponse::welcome());
    }

    public function onReceive(SwooleServer $server, int $fd, int $fromId, string $data): void
    {
        parent::onReceive( $server,  $fd, $fromId, $data);
    }

    public function onClose(SwooleServer $Server, int $from_id, int $reactor_id)
    {

    }
}
