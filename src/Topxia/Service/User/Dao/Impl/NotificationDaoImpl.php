<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\NotificationDao;

class NotificationDaoImpl extends BaseDao implements NotificationDao
{
    protected $table = 'notification';

    public function addNotification($notification)
    {
        $affected = $this->getConnection()->insert($this->table, $notification);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert notification error.');
        }
        return $this->getNotification($this->getConnection()->lastInsertId());
    }

    public function getNotification($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function updateNotification($id, $fields)
    {
        return $this->update($id, $fields);
    }

    public function findNotificationsByUserId($userId, $start, $limit)
    {
        $builder = $this->createQueryBuilder()
            ->select('*')->from($this->table, 'notification')
            ->where("userId = :userId")
            ->orderBy('createdTime', 'ASC')
            ->setParameter(":userId", $userId)
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function getNotificationCountByUserId($userId)
    {
        return $this->createQueryBuilder()
            ->select('COUNT(*)')->from($this->table, 'notification')
            ->where("userId = :userId")
            ->setParameter(":userId", $userId)
            ->execute()
            ->fetchColumn(0);
    }
}