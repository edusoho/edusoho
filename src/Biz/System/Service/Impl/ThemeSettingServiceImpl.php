<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\System\Service\ThemeSettingService;

class ThemeSettingServiceImpl extends BaseService implements ThemeSettingService
{
    public function isSupportGetUserById()
    {
        $theme = $this->getSettingService()->get('theme');

        if(in_array($theme['code']??'jianmo', ['jianmo', 'default', 'defaultb', 'graceful', 'autumn', 'certificate'])){
            return false;
        }

        return true;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}