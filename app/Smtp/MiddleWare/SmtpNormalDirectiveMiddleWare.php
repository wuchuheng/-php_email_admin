<?php

/**
 *  这是第2层:smtp指令回复层.对指令做出正确的回复
 *
 *  @author wuchuheng <wuchuheng@163.com>
 *  @licence MIT
 */

namespace App\Smtp\MiddleWare;

use App\Smtp\Util\Session;
use Hyperf\JsonRpc\ResponseBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpMessage\Server\Response as Psr7Response;

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
        $this->Response = new Psr7Response();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
