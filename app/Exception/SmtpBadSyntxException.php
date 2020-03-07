<?php

/**
 *  the exception for the mesage is legal.
 */
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class SmtpBadSyntxException extends SmtpBaseException
{
    public $msg = 'Error: bad syntax';
    public $code = 500;
}
