<?php

namespace Biz\Util\Service;

interface MobileDeviceService
{
    function addMobileDevice($parames);
    function findMobileDeviceByIMEI($imei);
}
