<?php

/**
 * 信封验证.
 * Author Wuchuheng<wuchuheng@163.com>
 * Licence MIT
 * DATE 2020/3/9
 */
namespace App\Smtp\Validate;

use App\Exception\SmtpBadSyntxException;

class MailerValidate extends BaseValidate
{
    /**
     * 验证.
     *
     * @param int $fd
     * @param string $msg
     */
    public function goCheck(int $fd, string $msg): void
    {
        $patten = "/<([a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)+)>/";
        if (preg_match($patten, $msg, $result)) {
            if(count($result) !== 2) {
                throw new SmtpBadSyntxException();
            }
        } else {
            throw new SmtpBadSyntxException();
        }
    }
}
