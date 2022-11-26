<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingUpdateNotifyService;

class SettingUpdateNotifyServiceImpl extends BaseService implements SettingUpdateNotifyService
{
    public function notifyLogoUpdate()
    {
        $this->dispatchEvent('setting.school.logo.update', null);
    }

    public function notifyWapUpdate()
    {
        $this->dispatchEvent('setting.wap.update', null);
    }
}
