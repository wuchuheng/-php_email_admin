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

return [
    \App\Smtp\Listener\SmtpServerStartListener::class,
    \App\Smtp\Listener\HeloListener::class,
    \App\Smtp\Listener\QuitListener::class,
    \App\Smtp\Listener\MailFromListener::class
];
