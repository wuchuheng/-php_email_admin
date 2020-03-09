<?php

/**
 * 招呼验证.
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */
namespace App\Smtp\Validate;

use App\Exception\SmtpBadSyntxException;

class HeloValidate extends BaseValidate
{
    /**
     * 验证.
     * @param int $fd
     * @param string $msg
     */
    public function goCheck(int $fd, string $msg): void
    {
        $msg = strtoupper($msg);
        $patten = "/^(?:HELO|EHLO)\s+\w+/";
        if (!preg_match($patten, $msg, $result)) {
            throw new SmtpBadSyntxException();
        }
    }
}
