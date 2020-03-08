<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use App\Smtp\MiddleWare\SmtpHelloMiddleWare;
use App\Smtp\MiddleWare\SmtpNormalDirectiveMiddleWare;

return [
    'http' => [
    ],
    'smtp' => [
        \App\Smtp\MiddleWare\SmtpUnnecessarilyMiddleWare::class, // 语法过虑
        \App\Smtp\MiddleWare\SmtpHelloMiddleWare::class, // 所有合法的指令的前提指令 HELLO过虑
        \App\Smtp\MiddleWare\SmtpNormalDirectiveMiddleWare::class, // 正常指令过虑
        \App\Smtp\MiddleWare\SmtpWriteMiddleWare::class, // 写信
    ]
];
