<?php

declare(strict_types=1);

/**
 *  这是第2层:smtp指令回复层.对指令做出正确的回复
 *
 *  @author wuchuheng <wuchuheng@163.com>
 *  @licence MIT
 */

namespace App\Smtp\MiddleWare;

use App\Smtp\Event\MailFromEvent;
use App\Smtp\Event\QuitEvent;
use App\Exception\{SmtpBadSequenceException};
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\JsonRpc\ResponseBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \App\Smtp\Util\Session;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use \App\Smtp\Event\HelloEvent;

    class SmtpReplyMiddleWare implements MiddlewareInterface
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
            // 写信正文模式(DATA)，进入下一层（写信层）
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
                case 'MAIL FROM':
                    $Response = $this->EventDispatcher->dispatch(new MailFromEvent($fd, $msg));
                    break;
                case 'RCPT TO':
                    //
                    break;
                default:
                    throw new SmtpBadSequenceException();
            }
            $response = new Psr7Response();
            return $response->withBody(new SwooleStream($Response->reply));
        }
    }
}
