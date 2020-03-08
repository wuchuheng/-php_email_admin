<?php

/**
 * 用于过虑发送的数据是否合法.
 *
 * @author wuchuheng <wuchuheng@163.com>
 */
namespace App\Smtp\MiddleWare;

use App\Smtp\Util\Session;
use Hyperf\Contract\ConfigInterface;
use Hyperf\JsonRpc\ResponseBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Exception\SmtpNotImplementedException;

class SmtpUnnecessarilyMiddleWare implements MiddlewareInterface
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
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     *
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /* $fd = $request->getAttribute('fd'); */
        /* $data = smtp_unpack($request->getAttribute('data')); */

        /* $status = $this->container->get(Session::class)->getStatusByFd($fd); */
        /* $is_edit = in_array($status, ['MAIL FROM', 'RCPT TO', 'DATA']); */
        /* $config = $this->container->get(ConfigInterface::class); */
        /* $is_dir = getDirectiveByMsg($data); */
        /* if ($is_edit || $is_dir) { */
            return $handler->handle($request);
        /* } else { */
        /*     throw new SmtpNotImplementedException(); */
        /* } */
    }
}
