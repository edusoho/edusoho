<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileStatusDao;
    
class UploadFileStatusDaoImpl extends BaseDao implements UploadFileStatusDao
{
	protected $table = 'upload_file_status';

	public function addUploadFileStatus(array $fields) 
	{
		$fields['createdTime'] = time();
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Upload File Status disk file error.');
        }
        return $this->getUploadFileStatus($this->getConnection()->lastInsertId());
	}

	public function updateUploadFileStatus($key, $fields)
	{
		$this->getConnection()->update($this->table, $fields, array('scopKey' => $key));
		return $this->getUploadFileStatusByKey($key);
	}

	public function getUploadFileStatus($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getUploadFileStatusByKey($key)
	{
		$sql = "SELECT * FROM {$this->table} WHERE scopKey = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($key)) ? : null;
	}

	public function deleteUploadFileStatus($key)
	{
		return $this->getConnection()->delete($this->table, array('scopKey' => $key));
	}
}