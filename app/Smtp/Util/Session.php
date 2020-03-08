<?php

namespace App\Smtp\Util;

use Hyperf\JsonRpc\ResponseBuilder;
use phpDocumentor\Reflection\Types\Mixed_;
use Psr\Container\ContainerInterface;

class Session
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(
        ContainerInterface $container
    ){
        $this->container = $container;
    }

    /**
     * get session data from redis.
     *
     * @param int $fd
     * @return array
     */
    public  function getAllByFd(int $fd)
    {
        $redis = $this->container->get(\Redis::class);
        $key = $this->getKey($fd);
        if ($redis->exists(config('smtp_session_prefix') . $fd)) {
            return [];
        } else {
            return $redis->key($key);
        }
    }

    /**
     * set the session data.
     * @param int $fd
     * @param string $data
     * @return bool
     */
    public function set(int $fd, string $hash_key, ?string $data)
    {
        $Redis = $this->container->get(\Redis::class);
        $key = $this->getKey($fd);
        return (bool) $Redis->hset($key, $hash_key, $data);
    }

    /**
     *  check the session has the key.
     *
     * @param int $fd
     * @param string $key
     * @return bool
     */
    public function has(int $fd, string $key)
    {
        $redis_key = $this->getKey($fd);
        $Redis = $this->container->get(\Redis::class);
        return $Redis->hExists($redis_key, $key);
    }

    /**
     * get connect session status.
     *
     * @param int $fd
     * @return string
     */
    public function getStatusByFd(int $fd): string
    {
        $redis_key = $this->getKey($fd);
        $Redis = $this->container->get(\Redis::class);
        if (!$Redis->exists($redis_key)) {
            return 'int';
        } else if (!$Redis->hExists($redis_key, 'status')) {
            return 'int';
        } else {
            return $Redis->hGet($redis_key, 'status');
        }

    }

    /**
     * get session key.
     *
     * @param int $fd
     * @return string
     */
    private function getKey(int $fd): string
    {
        return config('smtp_session_prefix') . $fd;
    }

    /**
     *  清空会话数据
     */
     public function removeAllByFd(int $fd): bool
     {
         $redis_key = $this->getKey($fd);
         $Redis = $this->container->get(\Redis::class);
         return (bool) $Redis->del($redis_key);
     }
}
