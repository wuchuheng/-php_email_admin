<?php
/**
 * 监听DATA指令事件并返回回复消息.
 *
 * @author wuchuheng<wuchuheng@163.com>
 */
namespace App\Smtp\Listener;

use App\Smtp\Util\Session;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use \Redis;
use App\Smtp\Event\{DataEvent, HelloEvent};
use App\Smtp\Validate\HeloValidate;
use App\Smtp\Server;

class DataListener implements ListenerInterface
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
            DataEvent::class
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
        $Session = $this->Container->get(Session::class);
        $Session->set($fd, 'status', 'DATA');
        $Session->set($fd, 'is_sequence', 0);
        $Event->reply = smtp_pack("354 End data with <CR><LF>.<CR><LF>");
        // server 的onReceive缓存下数据
        $Server = $this->Container->get(Server::class);
        $Server->data[$fd]['status'] = 'DATA';
    }
}
