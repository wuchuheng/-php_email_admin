<?php

namespace App\Smtp\Validate;

use App\Smtp\Server;
use App\Smtp\Service\ServerResponse;
use Swoole\Server as SwooleServer;

class CheckHelo
{
    /**
     * check the connect is hello ,when the directive is legal
     *
     */
    public static function goCheck(SwooleServer $Server, int $fd, &$ServerInstance, string $status): boolean
    {
        if (in_array($status, [
            'MAILI FROM',
        ]) && !$ServerInstance->is_helo) {
            $Server->send($fd, "503 Error: send HELO/EHLO first \r\n");
        return false;
        } else {
            return true;
        }
    }
}
