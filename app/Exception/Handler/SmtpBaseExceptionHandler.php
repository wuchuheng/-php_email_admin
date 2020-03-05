<?php
namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FooException;
use Swoole\Server as SwooleServer;

use Throwable;

class SmtpBaseExceptionHandler
{
    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $Throwable, SwooleServer $Server)
    {
        var_dump(11111111);
        $Server->send($Throwable->fd, $Throwable->code .  " {$Throwable->message} \r\n");
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
