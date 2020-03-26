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
    \App\Smtp\Util\Session::class    => \App\Smtp\Util\Session::class,
    \PhpMimeMailParser\Parser::class => \PhpMimeMailParser\Parser::class,
    \App\Smtp\Server::class          => \App\Smtp\Server::class,
    \App\Smtp\Util\Session::class    =>  \App\Smtp\Util\Session::class
];
