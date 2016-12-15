<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\NotificationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class NotificationDaoImpl extends GeneralDaoImpl implements NotificationDao
{
    protected $table = 'notification';

    public function searchByUserId($userId, $start, $limit)
    {
        return $this->search(array('userId' => $userId), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'content' => 'json'
            ),
            'conditions' => array(
                'userId = :userId'
            ),
            'orderbys'   => array(
                'createdTime'
            )
        );
    }
}
