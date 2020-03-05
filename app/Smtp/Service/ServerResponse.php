<?php

/**
 * this file is part of hyperf_email.
 *
 * @author wuchuheng <root@wuchuheng.com>
 *
 */
namespace App\Smtp\Service;

class ServerResponse
{
    /**
     * Success reply code
     */
    private static $success_code = 220;

        
    /**
     *  welcome message
     */
    public static function welcome()
    {
        return self::$success_code . " Hyperf Email System \r\n";
    }

    /**
     *  get message by code.
     * 
     * @code email reply code
     */
    public static function getDefaultMessageByCode(int $code): string
    {
        $codes = [
            220 => "OK \r\n",
            502 => "Error: command not implemented \r\n",
            500 => "Error: bad syntax \r\n",
        ];
        return $codes[$code];
    }
}

