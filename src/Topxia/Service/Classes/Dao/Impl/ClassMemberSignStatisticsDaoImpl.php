<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassMemberSignStatisticsDao;

class ClassMemberSignStatisticsDaoImpl extends BaseDao implements ClassMemberSignStatisticsDao
{
	protected $table = 'class_member_sign_statistics';

	public function addClassMemberSignStatistics($ClassMemberSignStatistics)
	{
        $affected = $this->getConnection()->insert($this->table, $ClassMemberSignStatistics);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert class sign Statistics error.');
        }
        return $this->getClassMemberSignStatisticsById($this->getConnection()->lastInsertId());
	}

	public function getClassMemberSignStatisticsById($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getClassMemberSignStatistics($userId, $classId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ?  and classId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $classId)) ? : null;
	}

	public function updateClassMemberSignStatistics($userId, $classId, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('userId' => $userId, 'classId' => $classId));
        return $this->getClassMemberSignStatistics($userId, $classId);
	}
}
