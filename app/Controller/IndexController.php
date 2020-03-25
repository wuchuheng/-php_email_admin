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

namespace App\Controller;

use App\Exception\SmtpNotImplementedException;

class IndexController extends AbstractController
{
    public function index()
    {
        return $this->response->json(111);
    }

    public function excption()
    {
        var_dump(1);
    }
}
