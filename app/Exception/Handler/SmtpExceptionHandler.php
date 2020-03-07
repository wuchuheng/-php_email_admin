<?php
namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use App\Exception\SmtpBaseException;

class SmtpExceptionHandler extends  ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        if ($throwable instanceof SmtpBaseException) {
            $msg = $throwable->code . ' ' . $throwable->msg;
        } else {
            $msg = '500 sorry，we make a mistake. (^o^)Y';
        }
        $msg = smtp_pack($msg);
        return new SwooleStream($msg);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
