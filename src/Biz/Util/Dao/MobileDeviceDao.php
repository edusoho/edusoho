<?php

namespace Biz\Util\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MobileDeviceDao extends GeneralDaoInterface
{
    public function addMobileDevice(array $parames);

    public function getMobileDeviceById($id);

    public function getMobileDeviceByIMEI($imei);
}
