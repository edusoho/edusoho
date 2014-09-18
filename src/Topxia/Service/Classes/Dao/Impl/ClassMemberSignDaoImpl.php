<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassMemberSignDao;

class ClassMemberSignDaoImpl extends BaseDao implements ClassMemberSignDao
{
	protected $table = 'class_member_sign';

	public function addClassMemberSign($ClassMemberSign)
	{
        $affected = $this->getConnection()->insert($this->table, $ClassMemberSign);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user sign error.');
        }
        return $this->getClassMemberSign($this->getConnection()->lastInsertId());
	}

	public function getClassMemberSign($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findClassMemberSignByPeriod($userId, $classId, $startTime, $EndTime)
	{
		$sql ="SELECT * FROM {$this->table} WHERE userId = ? and classId = ? and createdTime > ? and createdTime < ?;";
        return $this->getConnection()->fetchAll($sql, array($userId, $classId, $startTime, $EndTime)) ? : null;
	}
}
