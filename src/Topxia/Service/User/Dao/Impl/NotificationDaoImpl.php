<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\NotificationDao;

class NotificationDaoImpl extends BaseDao implements NotificationDao
{
    protected $table = 'notification';

    public function getNotification($id)
    {
        $that = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        });
    }

    public function addNotification($notification)
    {
        $affected = $this->getConnection()->insert($this->table, $notification);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert notification error.');
        }
        $this->clearCached();
        return $this->getNotification($this->getConnection()->lastInsertId());
    }

    public function updateNotification($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getNotification($id);
    }

    public function findNotificationsByUserId($userId, $start, $limit)
    {
        $that = $this;
        $this->filterStartLimit($start, $limit);
        return $this->fetchCached("userId:{$userId}:start:{$start}:limit:{$limit}", $userId, $start, $limit, function ($userId, $start, $limit) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
            return $that->getConnection()->fetchAll($sql, array($userId));
        });
    }

    public function getNotificationCountByUserId($userId)
    {
        $that = $this;
        return $this->fetchCached("userId:{$userId}:count", $userId, function ($userId) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE  userId = ? ";
            return $that->getConnection()->fetchColumn($sql, array($userId));
        });
    }

    public function searchNotifications($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createNotificationQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        
        $keys = $this->generateKeyWhenSearch($conditions, $orderBy, $start, $limit);
        
        return $this->fetchCached($keys, $builder, function ($builder) {
            return $builder->execute()->fetchAll() ? : array();
        });
    }

    public function searchNotificationCount($conditions)
    {
        $builder = $this->createNotificationQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function deleteNotification($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    protected function createNotificationQueryBuilder($conditions)
    {
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'notification')
            ->andWhere('userId = :userId')
            ->andWhere('type = :type');
    }
    
}