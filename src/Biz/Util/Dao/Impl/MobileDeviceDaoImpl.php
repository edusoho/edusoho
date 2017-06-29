<?php

namespace Biz\Util\Dao\Impl;

use Biz\Util\Dao\MobileDeviceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MobileDeviceDaoImpl extends GeneralDaoImpl implements MobileDeviceDao
{
    protected $table = 'mobile_device';

    public function getMobileDeviceById($id)
    {
        return $this->get($id);
    }

    public function addMobileDevice(array $parames)
    {
        return $this->create($parames);
    }

    public function getMobileDeviceByIMEI($imei)
    {
        return $this->getByFields(array('imei' => $imei));
    }

    public function declares()
    {
        return array();
    }
}
