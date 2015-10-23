<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\FileGroupDao;

class FileGroupDaoImpl extends BaseDao implements FileGroupDao
{
    protected $table = 'file_group';

	public function getGroup($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findGroupByCode($code)
	{
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code)) ? : null;
	}

	public function findAllGroups()
	{
		$sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql);
	}

	public function addGroup($group)
	{
        if ($this->getConnection()->insert($this->table, $group) <= 0) {
            throw $this->createDaoException('Insert file group error.');
        }
        return $this->getGroup($this->getConnection()->lastInsertId());
	}

	public function deleteGroup($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

}