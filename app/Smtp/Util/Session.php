<?php

/**
 * 用于管理当前连接的会话数据.
 *
 */

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
     * get session key.
     *
     * @param int $fd
     * @return string
     */

    public function get(int $fd, string $hkey): string
    {
        $key = $this->getKey($fd);
        $Redis = $this->container->get(\Redis::class);
        $value = $Redis->hGet($key,$hkey);
        return $value;
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

    /**
     * 初始化连接会话数据
     * @param int $fd
     * @return bool
     */
    public function init(int $fd): bool
    {
        return (bool) $this->set($fd, 'is_hello', 0)
            && $this->set($fd, 'is_sequence', 0)
            && $this->set($fd, 'sequence_dirs', json_encode([]));
    }

    /**
     * 是否已经打招呼了
     *
     * @param int $fd
     * @return bool
     */
    public function isHello(int $fd): bool
    {
        return (bool) $this->get($fd, 'is_hello');
    }

    /**
     * 是否进入顺序模式
     *
     * @param int $fd
     * @return boole
     */
    public function isSequence(int $fd): bool
    {
        return (bool) $this->get($fd, 'is_sequence');
    }

    /**
     *  获取顺序指令合集
     *
     * @param int $fd
     * @return array
     */
    public function getSequenceDir(int $fd): array
    {
        $dirs = $this->get($fd, 'sequence_dirs');
        $dirs = json_decode($dirs, true);
        return $dirs;
    }
}
