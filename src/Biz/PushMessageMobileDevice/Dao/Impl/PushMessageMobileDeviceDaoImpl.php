<?php

namespace Biz\PushMessageMobileDevice\Dao\Impl;

use Biz\PushMessageMobileDevice\Dao\PushMessageMobileDeviceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PushMessageMobileDeviceDaoImpl extends GeneralDaoImpl implements PushMessageMobileDeviceDao
{
    protected $table = 'push_message_mobile_device';

    public function getByRegId($regId)
    {
        return $this->getByFields(array('regId' => $regId));
    }

    public function findByUserIds($userIds)
    {
        return $this->findInField('userId', $userIds);
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'id = :id',
                'userId = :userId',
                'userId IN (:userIds)',
                'regId = :regId',
                'regId IN (:regIds)',
            ),
        );
    }
}
