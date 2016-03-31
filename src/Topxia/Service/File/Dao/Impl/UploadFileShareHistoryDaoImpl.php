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
}