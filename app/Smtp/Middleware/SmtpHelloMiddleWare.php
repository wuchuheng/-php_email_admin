<?php

declare(strict_types=1);

/**
 *  if the client don't say “HELO"  befor other directive,then go back.
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
        if ($this->container->get(Session::class)->getStatusByFd($fd) === 'int') {
            if ($dir !== 'HELO') {
                throw new SmtpBaseException([
                    'msg' => 'Error: send HELO/EHLO first',
                    'code' => 503
                ]);
            }
            if (!preg_match('/^(:?HELO)\s+\w+/', $msg)) {
                throw new SmtpBadSyntxException();
            } else {
                $Session = $this->container->get(Session::class);
                $Session->set($fd, 'status', 'HELO');

            }
        }
        if ($dir === 'HELO') {
            $response = new Psr7Response();
            $reply = smtp_pack("250 OK");
            return $response->withBody(new SwooleStream($reply));
        }
        return $handler->handle($request);
    }
}
