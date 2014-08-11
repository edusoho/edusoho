<?php

namespace Topxia\Service\Util\Dao\Impl;

use Topxia\Service\Common\NewBaseDao;
use Topxia\Service\Util\Dao\MobileDeviceDao;

class MobileDeviceDaoImpl extends NewBaseDao implements MobileDeviceDao
{
	protected $table = 'client_device';

	public function addMobileDevice(array $parames)
	{
		return $this->insert($parames);
	}

    	public function findMobileDeviceByIMEI($imei)
    	{
    		return $this->fetchRow('imei = ?', array($imei));
    	}
}