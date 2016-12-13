<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\NotificationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class NotificationDaoImpl extends GeneralDaoImpl implements NotificationDao
{
    protected $table = 'notification';

    public function findByUserId($userId, $start, $limit)
    {
        return $this->findInField(array('userId' => $userId), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function countByUserId($userId)
    {
        return $this->count(array('userId' => $userId));
    }

    public function declares()
    {
        return array(
        );
    }
}
