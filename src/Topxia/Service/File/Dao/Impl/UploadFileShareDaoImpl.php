<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareDao;

class UploadFileShareDaoImpl extends BaseDao implements UploadFileShareDao
{

	protected $table = 'upload_files_share';

    public function getShare($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $file = $this->getConnection()->fetchAssoc($sql, array($id));
        return $file ? $this->createSerializer()->unserialize($file, $this->serializeFields) : null;

    }

    public function findSharesByTargetUserIdAndIsActive($targetUserId, $active = 1)
    {
		$sql = "SELECT DISTINCT sourceUserId FROM {$this->table} WHERE targetUserId = ? AND isActive = ?;";
		return $this->getConnection()->fetchAll($sql, array($targetUserId, $active)) ? : null;
    }

    public function findSharesBySourceUserId($sourceId)
    {
		$sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY updatedTime DESC;";
		return $this->getConnection()->fetchAll($sql, array($sourceUserId)) ? : null;
    }

    public function findShareBySourceIdAndTargetId($sourceId, $targetId)
    {
		$sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND targetUserId = ? LIMIT 1;";
		return $this->getConnection()->fetchAssoc($sql, array($sourceUserId, $targetUserId)) ? : null;
    }
	
	public function addShare($share)
	{
		$affected = $this->getConnection()->insert($this->table, $share);
		if ($affected <= 0) {
			throw $this->createDaoException('Insert file share error.');
		}
		return $this->getConnection()->lastInsertId();
	}

	public function updateShare($id, $fields)
	{
		$this->getConnection()->update($this->table, $fields, array('id' => $id));
		return $id;
	}

}
