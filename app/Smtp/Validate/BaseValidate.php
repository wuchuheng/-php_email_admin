<?php
/**
 * Created by PhpStorm
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */
namespace App\Smtp\Validate;

abstract class BaseValidate
{
    /**
     * 验证.
     *
     * @param int $fd
     * @param string $msg
     */
    abstract public function goCheck(int $fd, string $msg): void;
}
