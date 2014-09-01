<?php

namespace Topxia\Service\Util;

interface MobileDeviceService
{
    function addMobileDevice($parames);
    function findMobileDeviceByIMEI($imei);
}