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
use App\Smtp\Server;

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
             co(function() use($fd) {
                 // 收集邮件数据并导出eml
                 $Server = $this->container->get(Server::class);
                 $email_data = $Server->data[$fd]['data'];
                 $Server->data[$fd]['data']   = '';
                 $Server->data[$fd]['status'] = '';
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
                 $Email     = $this->Email->create([
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
                 $attachments = $Parser->getAttachments(false);
                 $relative_dir = 'uploads/' . $to_info[0]['address'] . '/' . uniqid();
                 $save_dir = BASE_PATH . '/public/' . $relative_dir;
                 is_dir($save_dir) || mkdir($save_dir, 0700, true);
                 foreach ($attachments as $attachment) {
                     $attachment->save($save_dir, Parser::ATTACHMENT_DUPLICATE_SUFFIX);
                     $relative_file_name = $relative_dir . '/' . $attachment->getFilename();
                     $file_name = $save_dir . '/' . $attachment->getFilename();
                     // 保存附件信息
                     $Attachment     = $this->Attachment->create([
                         'email_id' => $Email->id,
                         'file' => $relative_file_name,
                         'size' => filesize($file_name),
                         'type' => $attachment->getContentType(),
                         'name' => $attachment->getFilename()
                     ]);
                 }
             });
        } else {
            $reply = '';
        }
        return $response->withBody(new SwooleStream($reply));
    }
}

