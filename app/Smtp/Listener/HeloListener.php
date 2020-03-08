<?php
/**
 * 监听HELO OR EHELO指令事件并返回回复消息.
 *
 * @author wuchuheng<wuchuheng@163.com>
 */
namespace App\Smtp\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use PhpCsFixer\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use \Redis;
use \App\Smtp\Event\{
    HelloReply
};

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

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
        $this->Redis = $container->get(\Redis::class);
    }

    public function listen(): array
    {
        return [
            HelloReply::class
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $Event)
    {
        var_dump($Event->fd, $Event->msg);
    }
}
