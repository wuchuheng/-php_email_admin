<?php
/**
 * 信封的收件地址事件(RCPT TO)
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */

namespace App\Smtp\Listener;

use App\Smtp\Util\Session;
use App\Smtp\Validate\MailerValidate;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use \Redis;
use App\Smtp\Event\{RcptToEvent};

class RcptToListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Redis
     */
    private $Redis;

    /**
     * @Inject()
     * @var Session
     */
    private  $Session;


    private $Container;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
        $this->Redis = $container->get(\Redis::class);
        $this->Container = $container;
    }

    public function listen(): array
    {
        return [
            RcptToEvent::class
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $Event)
    {
        $msg = $Event->msg;
        $fd = $Event->fd;
        $dir = getDirectiveByMsg($msg);
        // 验证信封
        (new MailerValidate())->goCheck($fd, $msg);
        $this->Session->set($fd, 'status', $dir);
        $this->Session->set($fd, 'is_sequence', 1);
        $this->Session->set($fd, 'sequence_dirs', json_encode(['RCPT TO', 'QUIT', 'DATA']));
        $Event->reply = smtp_pack("250 MAIL OK");
        $this->Session->cacheEmail($fd, smtp_pack($msg));
    }
}
