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
        $this->clearCache();
        return $this->getNotification($this->getConnection()->lastInsertId());
    }

    public function updateNotification($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCache();
        return $this->getNotification($id);
    }

    public function findNotificationsByUserId($userId, $start, $limit)
    {
        $that = $this;
        return $this->fetchCached("userId:{$userId}:start:{$start}:limit:{$limit}", $userId, $start, $limit, function ($userId, $start, $limit) use ($that) {
            $that->filterStartLimit($start, $limit);
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
        $that = $this;

        $keys = 'search';
        foreach ($conditions as $key => $value) {
            $keys = $keys.":{$key}:{$value}";
        }

        $keys = $keys.":{$orderBy[0]}:{$orderBy[1]}:start:{$start}:limit:{$limit}";

        return $this->fetchCached($keys, $conditions, $orderBy, $start, $limit, function ($conditions, $orderBy, $start, $limit) use ($that) {
            $that->filterStartLimit($start, $limit);
            $builder = $that->createNotificationQueryBuilder($conditions)
                ->select('*')
                ->orderBy($orderBy[0], $orderBy[1])
                ->setFirstResult($start)
                ->setMaxResults($limit);
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
        $this->clearCache();
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