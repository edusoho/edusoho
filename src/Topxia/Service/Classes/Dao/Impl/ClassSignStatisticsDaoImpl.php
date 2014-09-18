<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\ClassSignStatisticsDao;

class ClassSignStatisticsDaoImpl extends BaseDao implements ClassSignStatisticsDao
{
	protected $table = 'class_sign_statistics';

	public function addClassSignStatistics($classSignStatistics)
	{
        $affected = $this->getConnection()->insert($this->table, $classSignStatistics);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert class sign Statistics error.');
        }
        return $this->getClassSignStatisticsById($this->getConnection()->lastInsertId());
	}

	public function getClassSignStatisticsById($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getClassSignStatisticsByClassId($classId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE classId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($classId)) ? : null;
	}

	public function updateClassSignStatistics($classId, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('classId' => $classId));
        return $this->getClassSignStatisticsByClassId($classId);
	}
}
