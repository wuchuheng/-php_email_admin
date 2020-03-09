<?php
namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use App\Exception\SmtpBaseException;
use Hyperf\Logger\LoggerFactory;

class SmtpExceptionHandler extends  ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger, LoggerFactory $loggerFactory)
    {
        $this->logger = $logger;
        $this->log = $loggerFactory->get('default');
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        if ($throwable instanceof SmtpBaseException) {
            $msg = $throwable->code . ' ' . $throwable->msg;
        } else {
            // 登记系统内部异常.
            $this->log->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->log->error($throwable->getTraceAsString());
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
