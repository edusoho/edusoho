<?php

namespace Biz\Util\Service;

interface MobileDeviceService
{
    public function addMobileDevice($parames);

    public function findMobileDeviceByIMEI($imei);
}
