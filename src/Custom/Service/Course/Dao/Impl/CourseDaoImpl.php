<?php

namespace Custom\Service\Course\Dao\Impl;

use Topxia\Service\Course\Dao\Impl\CourseDaoImpl as BaseCourseDao;

class CourseDaoImpl extends BaseCourseDao
{
	public function getPeriodicCoursesCount($rootId){
		$sql = "SELECT COUNT(*) FROM {$this->getTablename()} WHERE rootId = ?";
		return $this->getConnection()->fetchColumn($sql, array($rootId)) ? : null;
	}

	public function findOtherPeriods($course){
		$rootId = ($course['rootId']==0 ? $course['id'] : $course['rootId']);
		$sql ="SELECT * FROM {$this->getTablename()} WHERE rootId = {$rootId} and id != {$course['id']};";
		return $this->getConnection()->fetchAll($sql);
	}

}