<?php
/**
 * 监听HELO OR EHELO指令事件并返回回复消息.
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
    use \App\Smtp\Event\{
    HelloEvent
};
use  App\Smtp\Validate\HeloValidate;

class HeloListener implements ListenerInterface
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
            HelloEvent::class
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
        (new HeloValidate())->goCheck($fd, $msg);
        $Session = $this->Container->get(Session::class);
        $Session->set($fd, 'status', 'HELO');
        $Session->set($fd, 'is_hello', 1);
        $Event->reply = smtp_pack("250 OK");
    }
}
