<?php

namespace Biz\Util\Service\Impl;

use Biz\BaseService;
use Biz\Util\Service\MobileDeviceService;

class MobileDeviceServiceImpl extends BaseService implements MobileDeviceService
{
    public function addMobileDevice($parames)
    {
        if ($this->findMobileDeviceByIMEI($parames['imei'])) {
            return false;
        }
        $mobileDevice = $this->getMobileDeviceDao()->addMobileDevice($parames);

        return !empty($mobileDevice);
    }

    public function findMobileDeviceByIMEI($imei)
    {
        return $this->getMobileDeviceDao()->getMobileDeviceByIMEI($imei);
    }

    protected function getMobileDeviceDao()
    {
        return $this->createDao('Util:MobileDeviceDao');
    }
}
