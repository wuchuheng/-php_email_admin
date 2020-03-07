<?php

declare(strict_types=1);

/**
 * Smtp service proces code.
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

    public function __construct(
        ContainerInterface $container
    ){
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute('data');
        $dir = getDirectiveByMsg($data);
        $response = new Psr7Response();
        switch ($dir) {
            case "MAIL FROM":
                $reply = smtp_pack("250 mail OK");
                break;
            case "RCPT to":
                $reply = smtp_pack("250 mail Ok");
                break;
            case "DATA":
                $reply = smtp_pack("354 End data with <CR><LF>.<CR><LF>");
        }
        if (!$dir) {
            $reply = smtp_pack("250 mail Ok");
        }
        var_dump(smtp_unpack($data));
        return $response->withBody(new SwooleStream($reply));
    }
}
