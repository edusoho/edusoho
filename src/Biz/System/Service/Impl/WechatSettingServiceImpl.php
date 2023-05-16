<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\System\Service\WechatSettingService;

class WechatSettingServiceImpl extends BaseService implements WechatSettingService
{
    const NAME = 'wechat';

    public function set($value)
    {
        $this->getSettingService()->set(self::NAME, $value);
        $setting = $this->getSettingService()->get(self::NAME);
        $this->dispatchEvent('setting.wechat.set', $setting);

        return $setting;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
