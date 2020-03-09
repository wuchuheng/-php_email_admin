<?php

/**
 * 这是第一层: 过虑层.用于过滤合法的数据.
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
use App\Exception\{
    SmtpNotImplementedException,
    SmtpBadSequenceException
};
use \App\Smtp\Validate\{
    MailerValidate
};

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
        $fd = $request->getAttribute('fd');
        $msg = smtp_unpack($request->getAttribute('data'));
        $status = $this->container->get(Session::class)->getStatusByFd($fd);
        $is_dir = getDirectiveByMsg($msg);
        $Session = $this->container->get(Session::class);
        // 是合法命令行，或者编辑状态就放行
        if ($status === 'DATA' || $is_dir) {
            // 检测连接会话是否打过招呼
            if (!$Session->isHello($fd) && !in_array($is_dir, ['HELO', 'EHLO'])) {
                throw new SmtpBadSequenceException([
                    'msg' => 'send HELO/EHLO first'
                ]);
            }
            // 检测是否有顺指令
            if ($Session->isSequence($fd) && !in_array($is_dir, $Session->getSequenceDir($fd))) {
                throw new SmtpBadSequenceException();
            }
            // 放行，进入下一层
            return $handler->handle($request);
        } else {
            throw new SmtpNotImplementedException();
        }
    }
}
