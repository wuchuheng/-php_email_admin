<?php

namespace App\Smtp\Validate;

use App\Smtp\Server;
use App\Smtp\Service\ServerResponse;

class CheckStatus
{
    /**
     * Email directives
     */
    private $dir_list = [
        'HELO',
        'MAILI FROM',
        'RCPT TO',
        'DATA',
        'QUIT',
        'REST',
        'VRFY',
        'EXPN',
        'HELP'
    ];

    /**
     * get the connection session status.
     *
     */
    public static function getSessionStatus(Server &$ServerInstance, String $message): Array
    {
        [$dir, ] = explode(' ', $message);
        if (strtoupper($dir) === 'HELO') {
            return self::checkHeloDir($message);
        } elseif(self::isMaillFrom($message)) {
            return [
                'status' => 'SYNTAX',
                'code' => 500,
                'message' => ServerResponse::getDefaultMessageByCode(500)
            ];
        } else { 
            return 'ERROR';
        }
    }

    /**
     * Validate the directive is 'mail form' by message.
     * 
     * @return boolean
     */
    private static function isMaillFrom(string $message)
    {
        return  true;
    }

    /**
     * validate helo directive.
     *
     */
    public static function checkHeloDir($message)
    {
        [$dir, ] = explode(' ', $message);
        $is_param = str_replace($dir, '', trim($message));
        if ($is_param) {
            return [
                'status' => 'HELO',
                'code' => 220,
                'message' => ServerResponse::getDefaultMessageByCode(220)
            ];
        } else {
            return [
                'status' => 'SYNTAX',
                'code' => 500,
                'message' => ServerResponse::getDefaultMessageByCode(500)
            ];
        }
    }
    
}
