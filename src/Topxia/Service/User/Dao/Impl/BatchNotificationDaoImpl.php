<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\BatchNotificationDao;

class BatchNotificationDaoImpl extends BaseDao implements BatchNotificationDao
{
    protected $table = 'batch_notification';

    public function getBatchNotification($id)
    {
        $that = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

    public function addBatchNotification($batchNotification)
    {
        $affected = $this->getConnection()->insert($this->table, $batchNotification);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert batchNotification error.');
        }
        $this->clearCached();
        return $this->getBatchNotification($this->getConnection()->lastInsertId());
    }

    public function searchBatchNotificationCount($conditions)
    {
        if (isset($conditions['content'])) {
            if (empty($conditions['content'])) {
                unset($conditions['content']);
            } else {
                $conditions['content'] = "%{$conditions['content']}%";
            }
        }

        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function deleteBatchNotification($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function updateBatchNotification($id, $batchNotification)
    {
        $this->getConnection()->update($this->table, $batchNotification, array('id' => $id));
        $this->clearCached();
        return $this->getBatchNotification($id);
    }

    public function searchBatchNotifications($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        if (empty($orderBy)) {
            $orderBy = array(
                'createdTime',
                'DESC'
            );
        }

        $builder = $this->_createSearchQueryBuilder($conditions)
                    ->select('*')
                    ->setFirstResult($start)
                    ->setMaxResults($limit)
                    ->orderBy($orderBy[0], $orderBy[1]);

        $keys = $this->generateKeyWhenSearch($conditions, $orderBy, $start, $limit);

        return $this->fetchCached($keys, $builder, function ($builder) {
            return $builder->execute()->fetchAll() ?: array();
        });
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
                    ->from($this->table, 'batchnotification')
                    ->andWhere('fromId = :fromId')
                    ->andWhere('title = :title')
                    ->andWhere('targetType = :targetType')
                    ->andWhere('targetId = :targetId')
                    ->andWhere('type = :type')
                    ->andWhere('createdTime > :createdTime')
                    ->andWhere('id > :id')
                    ->andWhere('content LIKE :content')
                    ->andWhere('published = :published');
    }
}
