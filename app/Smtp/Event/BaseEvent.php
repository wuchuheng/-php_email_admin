<?php

/**
 * HELO OR EHLO 指令应答事件.
 *
 *  @author wuchuheng <wuchuheng@163.com>
 *  @licence MIT
 */

namespace App\Smtp\Event;

class BaseEvent
{
    /**
     * 消息
     *
     */
    public $msg;

    /**
     * 连接标识
     *
     */

    public $fd;

    public function __construct(int $fd, string $msg)
    {
        $this->fd  = $fd;
        $this->msg = $msg;
    }
}
