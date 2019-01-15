<?php

namespace Biz\PushMessageMobileDevice\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\PushMessageMobileDevice\Service\PushMessageMobileDeviceService;

class PushMessageMobileDeviceServiceImpl extends BaseService implements PushMessageMobileDeviceService
{
    public function createPushMessageMobileDevice($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('regId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getPushMessageMobileDeviceDao()->create($fields);
    }

    public function updatePushMessageMobileDevice($id, $fields)
    {
        return $this->getPushMessageMobileDeviceDao()->update($id, $fields);
    }

    public function deletePushMessageMobileDevice($id)
    {
        return $this->getPushMessageMobileDeviceDao()->delete($id);
    }

    public function searchPushMessageMobileDevices($conditions, $orderBy, $start, $limit)
    {
        return $this->getPushMessageMobileDeviceDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getPushMessageMobileDevice($id)
    {
        return $this->getPushMessageMobileDeviceDao()->get($id);
    }

    public function getPushMessageMobileDeviceByRegId($regId)
    {
        return $this->getPushMessageMobileDeviceDao()->getByRegId($regId);
    }

    public function findPushMessageMobileDeviceByUserIds($userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        return $this->getPushMessageMobileDeviceDao()->findByUserIds($userIds);
    }

    /**
     * @return \Biz\PushMessageMobileDevice\Dao\Impl\PushMessageMobileDeviceDaoImpl
     */
    protected function getPushMessageMobileDeviceDao()
    {
        return $this->createDao('PushMessageMobileDevice:PushMessageMobileDeviceDao');
    }
}
