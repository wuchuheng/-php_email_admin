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

        $msg = smtp_unpack($request->getAttribute('data'));
        $dir = getDirectiveByMsg($msg);


        $fd = $request->getAttribute('fd');
        $msg = smtp_unpack($request->getAttribute('data'));
        $dir = getDirectiveByMsg($msg);
        // 断开应答
        if ($dir === 'QUIT') {
            $response = new Psr7Response();
            $reply = smtp_pack("221 Bye");
            return $response->withBody(new SwooleStream($reply));
        }
        // 打招呼应答
        if ($this->container->get(Session::class)->getStatusByFd($fd) === 'int') {
            if (!in_array($dir, ['EHLO', 'HELO'])) {
                throw new SmtpBaseException([
                    'msg' => 'Error: send HELO/EHLO first',
                    'code' => 503
                ]);
            }
            if (!preg_match('/^(:?HELO)|(:?EHLO)\s+\w+/', $msg)) {
                throw new SmtpBadSyntxException();
            } else {
                $Session = $this->container->get(Session::class);
                $Session->set($fd, 'status', 'HELO');

            }
        }
        if (in_array($dir, ['EHLO', 'HELO'])) {
            $response = new Psr7Response();
            $reply = smtp_pack("250 OK");
            return $response->withBody(new SwooleStream($reply));
        }
        

        return $handler->handle($request);
    }
}
