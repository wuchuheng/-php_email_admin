<?php
namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
class SmtpExceptionHandler extends  ExceptionHandler

{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        return new SwooleStream(get_class(new self()) . '500 Internal Server Error.');
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
