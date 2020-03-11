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
use App\Model\{
    Email,
    Attachment
};
use \PhpMimeMailParser\Parser;

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
        ContainerInterface $container,
        Email $Email,
        Attachment $Attachment,
        Parser $Parser
    ){
        $this->container  = $container;
        $this->Session    = $container->get(Session::class);
        $this->Email      = $Email;
        $this->Attachment = $Attachment;
        $this->Parser     = $Parser;
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
            // 收集邮件数据并导出eml
            $email_data = $this->Session->getCacheEmailData($fd);
            $relatively_path = config('email_save_dir') . "/" . $this->Session->get($fd, 'user') . '/' . date('Y-m-d-h-i-s') . '_' . uniqid() . '.eml';
            $file = BASE_PATH . $relatively_path;
            is_dir(dirname($file)) || mkdir(dirname($file), 0700, true);
            $fh = fopen($file, 'a+');
            fwrite($fh, $email_data, strlen($email_data));
            fclose($fh);
            $Parser = $this->Parser->setText($email_data);

            $to_info   = $Parser->getAddresses('to');
            $from_info = $Parser->getAddresses('from');
            $subject   = $Parser->getHeader('subject');
            $date      = $Parser->getHeader('date');
            $is_creade     = $this->Email->create([
                'to'        => $to_info[0]['address'],
                'to_name'   => $to_info[0]['display'],
                'from_name' => $from_info[0]['display'],
                'from'      => $from_info[0]['address'],
                'subject'   => $subject,
                'date'      => $date,
                'text'      => $Parser->getMessageBody('text'),
                'html'      => $Parser->getMessageBody('html'),
                'eml'       => $file,
            ]);
        } else {
            // 缓存邮件数据
            $this->Session->cacheEmailData($fd, smtp_pack($msg));
            $reply = '';
        }
        return $response->withBody(new SwooleStream($reply));
    }
}

