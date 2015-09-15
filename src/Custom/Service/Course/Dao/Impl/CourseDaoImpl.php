<?php

namespace Custom\Service\Course\Dao\Impl;

use Custom\Service\Course\Dao\CourseDao;
use Topxia\Service\Course\Dao\Impl\CourseDaoImpl as BaseCourseDao;

class CourseDaoImpl extends BaseCourseDao implements CourseDao
{
	public function getPeriodicCoursesCount($rootId){
		$sql = "SELECT COUNT(*) FROM {$this->getTablename()} WHERE rootId = ?";
		return $this->getConnection()->fetchColumn($sql, array($rootId)) ? : null;
	}

	public function findOtherPeriods($course){
		$rootId = $course['rootId']==0 ? $course['id'] : $course['rootId'];
		$sql ="SELECT * FROM {$this->getTablename()} WHERE rootId = {$rootId} and id != {$course['id']} and status= 'published';";
		return $this->getConnection()->fetchAll($sql);
	}

	public function addCourse($course)
	{
		$course = parent::addCourse($course);
		if($course['type'] == 'periodic' && $course['rootId'] == 0){
			$fields = array('rootId' => $course['id']);
			return parent::updateCourse($course['id'], $fields);
		}
		return $course;
	}


	protected function _createSearchQueryBuilder($conditions)
	{
		/*if(!isset($conditions['rootId'])){
			$conditions['rootId'] = 0;
		}*/
		$now = time();
		$builder = parent::_createSearchQueryBuilder($conditions)
			;

		if(!empty($conditions['table']) && $conditions['table'] == 'singleCourse'){
			$table = "(select a.* from (
						select b.*, {$now} - cast(b.startTime as signed) as maxTime, CASE b.rootId WHEN 0 or b.rootId is NULL THEN b.id ELSE b.rootId END as 'groupId' from course b order by maxTime desc) a
					group by groupId )";
			$builder->from($table, 'course');
		}

		return $builder;
	}


}