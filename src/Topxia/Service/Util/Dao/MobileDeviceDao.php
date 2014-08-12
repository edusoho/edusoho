<?php

namespace Topxia\Service\Util\Dao;

interface MobileDeviceDao
{
    public function addMobileDevice(array $parames);
    public function getMobileDeviceById($id);
    public function findMobileDeviceByIMEI($imei);
}