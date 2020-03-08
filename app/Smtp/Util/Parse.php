<?php

/**
 * 用于从数据当中解析或提取需要的数据.
 * 
 * @auth wuchuheng<wuchuheng@163.com>
 */

namespace App\Smtp\Util;


class Parse
{
    /**
     * 从字符串中解析出邮件地址
     *
     * @return string email
     */
    public function getEmailAddress(int $str)
    {
        $patten = "/<([a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)+)>/";
        if (preg_match($patten, $str, $result)) {
            return $result[1];
        } else {
            return false;
        }
    }
}
