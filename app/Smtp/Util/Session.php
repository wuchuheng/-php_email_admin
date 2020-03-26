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
     */
    public $data;

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
        return $this->data[$fd] ?? null;
    }

    /**
     * set the session data.
     * @param int $fd
     * @param string $data
     * @return bool
     */
    public function set(int $fd, string $hash_key, ?string $data)
    {
        $this->data[$fd][$hash_key] = $data;
        return true;
    }

    /**
     *  check the session has the key.
     *
     * @param int $fd
     * @param string $key
     * @return bool
     */
    public function has(int $fd, string $key) : bool
    {
        return (bool) isset($this->data[$fd][$key]);
    }

    /**
     * get connect session status.
     *
     * @param int $fd
     * @return string
     */
    public function getStatusByFd(int $fd): string
    {
        return $this->data[$fd]['status'] ?? 'init';
    }

    /**
     * get session key.
     *
     * @param int $fd
     * @return string
     */

    public function get(int $fd, string $hkey)
    {
       return $this->data[$fd][$hkey] ?? null;
    }

    /**
    *  清空会话数据
    */
    public function removeAllByFd(int $fd): bool
    {
       unset($this->data[$fd]);
    }

    /**
     * 初始化连接会话数据
     * @param int $fd
     * @return bool
     */
    public function init(int $fd): bool
    {
        $this->data[$fd]['is_hello'] = 0;
        $this->data[$fd]['is_sequence'] = 0;
        $this->data[$fd]['sequence_dirs'] = json_encode([]);
        $this->data[$fd]['email'] = '';
        return true;
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

    /**
     * 缓存邮件信封.
     *
     * @param int $fd
     * @param string $content
     * @return bool
     */
    public function cacheEmailer(int $fd, string $content = ''): bool
    {
        $email = $this->get($fd, 'emailer');
        $email .= $content;
        return (bool) $this->set($fd, 'emailer', $email);
    }

    /**
     * 连接会话数据
     * @param int $fd
     * @return array|mixed
     */
    private function _getConnectSessionByFd(int $fd): array
    {
        if(isset($_connect_session)) {
            return $_connect_session[$fd] ?? [];
        } else {
            global $_connect_session;
            $_connect_session[$fd] = [];
            return $_connect_session[$fd];
        }
    }
}
