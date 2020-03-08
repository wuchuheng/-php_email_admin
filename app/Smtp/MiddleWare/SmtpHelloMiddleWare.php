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
        // 已打过招呼则进行下一层
        return $handler->handle($request);
    }
}
