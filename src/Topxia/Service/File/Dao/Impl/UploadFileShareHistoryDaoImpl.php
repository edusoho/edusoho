<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareHistoryDao;

class UploadFileShareHistoryDaoImpl extends BaseDao implements UploadFileShareHistoryDao
{
	protected $table = 'upload_files_share_history';

	public function getShareHistory($id)
	{
		$sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
	}

	public function addShareHistory($shareHistory)
    {
        $affected = $this->getConnection()->insert($this->table, $shareHistory);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert file shareHistory error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function findShareHistoryByUserId($sourceUserId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY createdTime DESC;";
        return $this->getConnection()->fetchAll($sql, array($sourceUserId)) ?: null;
    }

    public function searchShareHistoryCount($conditions)
    {
        $builder = $this->createShareHistoryQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createShareHistoryQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
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
            ->from($this->table, 'upload_files_share_history')
            ->andWhere('sourceUserId = :sourceUserId')
            ->andWhere('targetUserId = :targetUserId')
            ->andWhere('id = :id')
            ->andWhere('isActive = :isActive');

        return $builder;
    }
}