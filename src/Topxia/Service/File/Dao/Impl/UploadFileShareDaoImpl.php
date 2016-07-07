<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareDao;

class UploadFileShareDaoImpl extends BaseDao implements UploadFileShareDao
{
    protected $table = 'upload_files_share';

    public function getShare($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findSharesByTargetUserIdAndIsActive($targetUserId, $active = 1)
    {
        $sql = "SELECT DISTINCT sourceUserId FROM {$this->table} WHERE targetUserId = ? AND isActive = ?;";
        return $this->getConnection()->fetchAll($sql, array($targetUserId, $active)) ?: null;
    }

    public function findShareHistoryByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY updatedTime DESC;";
        return $this->getConnection()->fetchAll($sql, array($sourceId)) ?: null;
    }

    public function findActiveShareHistoryByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND isActive = 1 ORDER BY updatedTime DESC;";
        return $this->getConnection()->fetchAll($sql, array($sourceId)) ?: null;
    }

    public function findShareHistory($sourceId, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND targetUserId = ? LIMIT 1;";
        return $this->getConnection()->fetchAssoc($sql, array($sourceId, $targetId)) ?: null;
    }

    public function searchShareHistoryCount($conditions)
    {
        $builder = $this->createShareHistoryQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchShareHistories(array $conditions, array $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime','updatedTime'));
        $builder = $this->createShareHistoryQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function addShare($share)
    {
        $affected = $this->getConnection()->insert($this->table, $share);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert file share error.');
        }

        return $this->getShare($this->getConnection()->lastInsertId());
    }

    public function updateShare($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getShare($id);
    }

    protected function createShareHistoryQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }

            return true;
        }

        );

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'upload_files_share')
            ->andWhere('sourceUserId = :sourceUserId')
            ->andWhere('targetUserId = :targetUserId')
            ->andWhere('id = :id')
            ->andWhere('isActive = :isActive');

        return $builder;
    }
}
