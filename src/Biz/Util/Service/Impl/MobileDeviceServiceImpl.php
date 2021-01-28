<?php

namespace Biz\Util\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Util\Service\MobileDeviceService;

class MobileDeviceServiceImpl extends BaseService implements MobileDeviceService
{
    public function addMobileDevice($params)
    {
        if (!ArrayToolkit::requireds($params, ['imei', 'platform'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $params = ArrayToolkit::parts($params, ['imei', 'platform', 'version', 'screenresolution', 'kernel']);
        if ($this->findMobileDeviceByIMEI($params['imei'])) {
            return false;
        }
        $mobileDevice = $this->getMobileDeviceDao()->addMobileDevice($params);

        return !empty($mobileDevice);
    }

    public function findMobileDeviceByIMEI($imei)
    {
        return $this->getMobileDeviceDao()->getMobileDeviceByIMEI($imei);
    }

    protected function getMobileDeviceDao()
    {
        return $this->createDao('Util:MobileDeviceDao');
    }
}
