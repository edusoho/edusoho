<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\BatchNotificationDao;

class BatchNotificationDaoImpl extends BaseDao implements BatchNotificationDao
{
    protected $table = 'batch_notification';

    public function getBatchNotification($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addBatchNotification($batchNotification)
    {
        $affected = $this->getConnection()->insert($this->table, $batchNotification);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert batchNotification error.');
        }

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
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function updateBatchNotification($id, $batchNotification)
    {
        $this->getConnection()->update($this->table, $batchNotification, array('id' => $id));
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

        return $builder->execute()->fetchAll() ?: array();
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
