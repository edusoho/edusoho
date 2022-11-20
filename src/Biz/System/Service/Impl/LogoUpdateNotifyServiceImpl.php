<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\LogoUpdateNotifyService;

class LogoUpdateNotifyServiceImpl extends BaseService implements LogoUpdateNotifyService
{

    public function notify()
    {
        $this->dispatchEvent('setting.school.logo.update', null);
    }
}
