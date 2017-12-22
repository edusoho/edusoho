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

    public function findBatchIdsByUserIdAndType($userId, $type)
    {
        $sql = "SELECT batchId FROM {$this->table} WHERE userId = ? and type = ? GROUP BY batchId";

        return $this->db()->fetchAll($sql, array($userId, $type));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'content' => 'json',
            ),
            'conditions' => array(
                'userId = :userId',
                'type = :type',
                'createdTime >= :createdTime_GT',
                'type IN (:types)',
                'isRead = :isRead',
            ),
            'orderbys' => array(
                'createdTime',
            ),
        );
    }
}
