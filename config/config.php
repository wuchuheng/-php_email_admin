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

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name' => env('APP_NAME', 'skeleton'),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    // smtp directive list.
    'smtp_directives' => [
        'EHLO',
        'HELO',
        'MAIL FROM',
        'RCPT TO',
        'DATA',
        'QUIT',
        'REST',
        'VRFY',
        'EXPN',
        'HELP'
    ],
    'smtp_session_prefix' => 'smtp_',
    // 邮件eml保存位置
    'email_save_dir'      => '/stages/emails'
];
