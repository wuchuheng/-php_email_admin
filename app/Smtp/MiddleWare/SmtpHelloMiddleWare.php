<?php

declare(strict_types=1);

/**
 *  连接端的smtp，打招呼和离开在这里应答.
 *
 */
namespace App\Smtp\MiddleWare;

use App\Smtp\Event\QuitEvent;
use App\Exception\{SmtpBadSequenceException, SmtpBaseException, SmtpBadSyntxException};
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
use \App\Smtp\Event\HelloEvent;

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
        $msg = smtp_unpack($request->getAttribute('data'));
        $dir = getDirectiveByMsg($msg);
        $status = $this->container->get(Session::class)->getStatusByFd($fd);
        if ($status === 'DATA') {
            // 写信正文模式，进入下一层（写信层）
            return $handler->handle($request);
        } else {
            // 指令应答
            switch ($dir) {
                case  'HELO':
                    $Response = $this->EventDispatcher->dispatch(new HelloEvent($fd, $msg));
                    break;
                case 'EHLO':
                    $Response = $this->EventDispatcher->dispatch(new HelloEvent($fd, $msg));
                    break;
                case 'QUIT':
                    $Response = $this->EventDispatcher->dispatch(new QuitEvent($fd, $msg));
                    break;
                default:
                    throw new SmtpBadSequenceException();
            }
            $response = new Psr7Response();
            return $response->withBody(new SwooleStream($Response->reply));
        }
    }
}
