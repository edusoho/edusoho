<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\NotificationDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class NotificationDaoImpl extends AdvancedDaoImpl implements NotificationDao
{
    protected $table = 'notification';

    public function searchByUserId($userId, $start, $limit)
    {
        return $this->search(['userId' => $userId], ['createdTime' => 'DESC'], $start, $limit);
    }

    public function findBatchIdsByUserIdAndType($userId, $type)
    {
        $sql = "SELECT batchId FROM {$this->table} WHERE userId = ? and type = ? GROUP BY batchId";

        return $this->db()->fetchAll($sql, [$userId, $type]);
    }

    public function declares()
    {
        return [
            'serializes' => [
                'content' => 'json',
            ],
            'conditions' => [
                'userId = :userId',
                'type = :type',
                'createdTime >= :createdTime_GT',
                'type IN (:types)',
                'isRead = :isRead',
            ],
            'orderbys' => [
                'createdTime',
            ],
            'timestamps' => ['createdTime'],
        ];
    }
}
