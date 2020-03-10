<?php

declare(strict_types=1);

/**
 * 邮件编辑处理
 *
 */
namespace App\Smtp\MiddleWare;

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
use \App\Smtp\Model\Email;

class SmtpWriteMiddleWare implements MiddlewareInterface
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
     * @var Session
     *
     */
    protected $Session;


    public function __construct(
        ContainerInterface $container
    ){
        $this->container = $container;
        $this->Session = $container->get(Session::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $msg = smtp_unpack($request->getAttribute('data'));
        $fd = $request->getAttribute('fd');
        $dir = getDirectiveByMsg($msg);
        $response = new Psr7Response();
        if ( $msg === '.' ) {
            $reply = smtp_pack('250 Mail Ok');
            $this->Session->set($fd, 'status', 'HELO');
        } else {
            $this->Session->cacheEmailData($fd, smtp_pack($msg));
            $reply = '';
        }
        return $response->withBody(new SwooleStream($reply));
    }
}

