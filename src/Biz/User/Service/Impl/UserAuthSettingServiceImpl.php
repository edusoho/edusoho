<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserAuthSettingService;

class UserAuthSettingServiceImpl extends BaseService implements UserAuthSettingService
{
    public function update($auth)
    {
        $this->getSettingService()->set('auth', $auth);
        $this->dispatch('user.auth.setting.update', $auth);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
