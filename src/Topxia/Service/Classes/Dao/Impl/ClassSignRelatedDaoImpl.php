<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassSignRelatedDao;

class ClassSignRelatedDaoImpl extends BaseDao implements ClassSignRelatedDao
{
	protected $table = 'class_sign_related';

	public function addClassSignRelated($classSignRelated)
	{
        $affected = $this->getConnection()->insert($this->table, $classSignRelated);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert class sign related error.');
        }
        return $this->getClassSignRelated($this->getConnection()->lastInsertId());
	}

	public function getClassSignRelated($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getUserClassSignRelated($classId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE classId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($classId)) ? : null;
	}

	public function updateClassSignRelated($classId, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('classId' => $classId));
        return $this->getUserClassSignRelated($classId);
	}
}
