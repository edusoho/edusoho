<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareDao;

class UploadFileShareDaoImpl extends BaseDao implements UploadFileShareDao {
	protected $table = 'upload_files_share';
	
	public function findMySharingContacts($targetUserId){
		$sql = "SELECT DISTINCT sourceUserId FROM {$this->table} WHERE targetUserId = ? and isActive = 1;";
		$result = $this->getConnection()->fetchAll($sql, array($targetUserId)) ? : null;
		return $result;
	}
	
	public function findShareHistoryByUserId($sourceUserId){
		$sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY updatedTime DESC;";
		$result = $this->getConnection()->fetchAll($sql, array($sourceUserId)) ? : null;
		return $result;
	}
	
	public function findShareHistory($sourceUserId, $targetUserId){
		$sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? and targetUserId = ? LIMIT 1;";
		$result = $this->getConnection()->fetchAssoc($sql, array($sourceUserId, $targetUserId)) ? : null;
		return $result;
	}
	
	public function addShare($share){
		$affected = $this->getConnection()->insert($this->table, $share);
		if ($affected <= 0) {
			throw $this->createDaoException('Insert file share error.');
		}
		return $this->getConnection()->lastInsertId();
	}

	public function updateShare($id, $fields) {
		$this->getConnection()->update($this->table, $fields, array('id' => $id));
		return $id;
	}

}
