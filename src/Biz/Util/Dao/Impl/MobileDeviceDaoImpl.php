<?php

namespace Biz\Util\Dao\Impl;

use Biz\Util\Dao\MobileDeviceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MobileDeviceDaoImpl extends GeneralDaoImpl implements MobileDeviceDao
{
    protected $table = 'mobile_device';

    public function getMobileDeviceById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? limit 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addMobileDevice(array $parames)
    {
        $affected = $this->getConnection()->insert($this->table, $parames);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert MediaParse error.');
        }
        return $this->getMobileDeviceById($this->getConnection()->lastInsertId());
    }

    public function findMobileDeviceByIMEI($imei)
    {
        $sql = "SELECT * FROM {$this->table} WHERE imei = ? limit 1";
        return $this->getConnection()->fetchAssoc($sql, array($imei));
    }

    public function declares()
    {
        return array();
    }
}
