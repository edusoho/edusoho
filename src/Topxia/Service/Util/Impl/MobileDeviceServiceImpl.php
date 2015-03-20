<?php
namespace Topxia\Service\Util\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Util\MobileDeviceService;

class MobileDeviceServiceImpl extends BaseService implements MobileDeviceService
{
	function addMobileDevice($parames)
	{
		if ($this->getMobileDeviceDao()->findMobileDeviceByIMEI($parames["imei"])) {
			return false;
		}
		$mobileDevice = $this->getMobileDeviceDao()->addMobileDevice($parames);
		return !empty($mobileDevice);
	}


    	function findMobileDeviceByIMEI($imei)
    	{
    		return $this->getMobileDeviceDao()->findMobileDeviceByIMEI($imei);
    	}

    	protected function getMobileDeviceDao ()
	{
	    return $this->createDao('Util.MobileDeviceDao');
	}
}