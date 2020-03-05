<?php

namespace App\Exception;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FooException;
use Swoole\Server as SwooleServer;
use Hyperf\Server\Exception\ServerException;

use Throwable;

class SmtpBaseException extends ServerException
{
    public $msg;

    public $fd;

    public $code;

    public function __construct($data)
    {
        $this->msg  = $data['msg'];
        $this->fd   = $data['fd'];
        $this->code = $data['code'];
    }
}

