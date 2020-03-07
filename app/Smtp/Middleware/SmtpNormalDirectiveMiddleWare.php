<?php

/**
 *  smtp 正常指令响应
 *
 */

namespace App\Smtp\MiddleWare;

use App\Smtp\Util\Session;
use Hyperf\JsonRpc\ResponseBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SmtpNormalDirectiveMiddleWare implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    public function __construct(
        ContainerInterface $container
    ){
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $msg = smtp_unpack($request->getAttribute('data'));
        $dir = getDirectiveByMsg($msg);
        $response = new Psr7Response();
        $fd = $request->getAttribute('fd');
//        $reply = smtp_pack("250 OK");
        $Session = $this->container->get(Session::class);
        $session_data =  $Session->getAllByFd($fd);

        return $handler->handle($request);
    }
}
