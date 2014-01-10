<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class TeacherTestDaoImpl extends BaseDao
{
	protected $table = "teacher_test";

	public function findTeacherTestsByTeacherId ($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE teacherId = ? ";
		return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
	}
}