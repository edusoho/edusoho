<?php

namespace Biz\PushDevice\Dao\Impl;

use Biz\PushDevice\Dao\PushDeviceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PushDeviceDaoImpl extends GeneralDaoImpl implements PushDeviceDao
{
    protected $table = 'push_device';

    public function getByRegId($regId)
    {
        return $this->getByFields(array('regId' => $regId));
    }

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function findByUserIds($userIds)
    {
        return $this->findInField('userId', $userIds);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
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
