<?php
/**
 * 监听MAIL FROM指令事件并返回回复消息.
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */

namespace App\Smtp\Listener;


use App\Smtp\Event\HelloEvent;
use App\Smtp\Event\MailFromEvent;
use App\Smtp\Util\Session;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class MailFromListener implements ListenerInterface
{
    /**
     * @var Redis
     */
    private $Redis;

    /**
     * @var Session
     */
    private  $Session;
    /**
     * @var ContainerInterface
     */
    private $Container;

    public function __construct(ContainerInterface $container)
    {
        $this->Redis = $container->get(\Redis::class);
        $this->Container = $container;
        $this->Session = $container->get(Session::class);
    }

    public function listen(): array
    {
        return [
            MailFromEvent::class
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
        $this->Session->set($fd, 'status', $dir);
        $this->Session->set($fd, 'is_sequence', 1);
        $this->Session->set($fd, 'sequence_dirs', json_encode(['RCPC TO']));
        $Event->reply = smtp_pack("250 MAIL OK");
    }
}
