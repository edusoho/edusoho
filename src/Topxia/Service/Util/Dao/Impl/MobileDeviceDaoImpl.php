<?php

namespace Topxia\Service\Util\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Util\Dao\MobileDeviceDao;

class MobileDeviceDaoImpl extends BaseDao implements MobileDeviceDao
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
}