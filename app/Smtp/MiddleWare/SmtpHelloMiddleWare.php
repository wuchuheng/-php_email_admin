<?php

declare(strict_types=1);

/**
 *  连接端的smtp，打招呼和离开在这里应答.
 *
 */
namespace App\Smtp\MiddleWare;

use App\Exception\{
    SmtpBaseException,
    SmtpBadSyntxException
};
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\JsonRpc\ResponseBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Redis\RedisFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use \App\Smtp\Util\Session;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use \App\Smpt\Event\{
    \App\Smpt\Event\
    HelloReply
};

class SmtpHelloMiddleWare implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    /**
    * @Inject()
    * @var EventDispatcherInterface
    */
    private $EventDispatcher;

    public function __construct(
        ContainerInterface $container
    ){
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $fd = $request->getAttribute('fd');
        $msg  = $request->getAttribute('msg');
        $this->EventDispatcher->dispatch(new HelloReply($fd, $msg));

        // 已打过招呼则进行下一层
        return $handler->handle($request);
    }
}
