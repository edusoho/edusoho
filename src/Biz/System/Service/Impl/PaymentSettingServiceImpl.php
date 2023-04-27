<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\LoginBindSettingService;
use Biz\System\Service\SettingService;

class PaymentSettingServiceImpl extends BaseService implements LoginBindSettingService
{
    const BASE_NAME = 'payment';

    public function get($default = [])
    {
        return $this->getSettingService()->get(self::BASE_NAME, $default);
    }

    public function set($value)
    {
        $this->getSettingService()->set(self::BASE_NAME, $value);
        $this->dispatchEvent('payment.setting.set', $this->get(self::BASE_NAME));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
