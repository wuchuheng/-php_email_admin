<?php

/**
 *  错误顺序指令异常
 */
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class SmtpBadSequenceException extends SmtpBaseException
{
    public $msg = 'bad sequence of commands';
    public $code = 503;
}
