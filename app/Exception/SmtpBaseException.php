<?php

namespace App\Exception;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\SmtpNotImplementedException;
use Swoole\Server as SwooleServer;
use Hyperf\Server\Exception\ServerException;

use Throwable;

class SmtpBaseException extends ServerException
{
    public $msg;

    public $code;

    public function __construct(array $data = [])
    {
        if (array_key_exists('msg', $data)) {
            $this->msg  = $data['msg'];
        }
        if (array_key_exists('code', $data)) {
            $this->code = $data['code'];
        }
    }
}

