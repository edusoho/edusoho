<?php

namespace Biz\PushDevice\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\PushDevice\Service\PushDeviceService;

class PushDeviceServiceImpl extends BaseService implements PushDeviceService
{
    public function getPushSdk()
    {
        return $this->biz['qiQiuYunSdk.push'];
    }

    public function createPushDevice($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('regId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getPushDeviceDao()->create($fields);
    }

    public function updatePushDevice($id, $fields)
    {
        return $this->getPushDeviceDao()->update($id, $fields);
    }

    public function deletePushDevice($id)
    {
        return $this->getPushDeviceDao()->delete($id);
    }

    public function searchPushDevices($conditions, $orderBy, $start, $limit)
    {
        return $this->getPushDeviceDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getPushDevice($id)
    {
        return $this->getPushDeviceDao()->get($id);
    }

    public function getPushDeviceByRegId($regId)
    {
        return $this->getPushDeviceDao()->getByRegId($regId);
    }

    public function getPushDeviceByUserId($userId)
    {
        return $this->getPushDeviceDao()->getByUserId($userId);
    }

    public function findPushDevicesByUserId($userId)
    {
        return $this->getPushDeviceDao()->findByUserId($userId);
    }

    public function findPushDeviceByUserIds($userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        return $this->getPushDeviceDao()->findByUserIds($userIds);
    }

    /**
     * @return \Biz\PushDevice\Dao\Impl\PushDeviceDaoImpl
     */
    protected function getPushDeviceDao()
    {
        return $this->createDao('PushDevice:PushDeviceDao');
    }
}
