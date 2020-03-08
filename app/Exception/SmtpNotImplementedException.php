<?php

/**
 *  the exception for the mesage is legal.
 */
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class SmtpNotImplementedException extends SmtpBaseException
{
    public $msg = 'Error: command not implemented';
    public $code = 502;
}
