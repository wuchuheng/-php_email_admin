<?php

use function foo\func;

/**
 * unpack smtp message
 * @param string $data
 * @return bool|string
 *
 */
function smtp_unpack($data): string
{
    if (is_array($data) && array_key_exists('data', $data)) {
        return substr($data['data'], 0, -2);
    }  else {
        return '';
    }
}

/**
 * Pack Smtp data packet.
 *
 * @param string $message
 * @return string
 */
function smtp_pack(string $message): string
{
    return $message . "\r\n";
}

/**
 * @param string $msg
 * @return mixed
 */
function getDirectiveByMsg(string $msg = '')
{
    if ($msg === '') {
        return '';
    } else {
        $msg = strtoupper($msg);
        $smtp_directives = config('smtp_directives');
        foreach ($smtp_directives as &$el) {
            $el = ':?' . $el;
        }
        $partten = "/^(" . implode('|', $smtp_directives) . ")/";
        (bool) $is_match = preg_match($partten, $msg, $result);
        if ($is_match) {
            return reset($result);
        } else {
            return false;
        }
    }
}
