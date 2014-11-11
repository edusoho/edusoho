<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseNoteDao;
use Topxia\Service\Course\Dao\CourseFileDao;

class CourseFileDaoImpl extends BaseDao implements CourseFileDao {
	protected $table = 'course_file';
	
	public function addCourseFile($courseFileFields) {
		$affected = $this->getConnection ()->insert ( $this->table, $courseFileFields );
		if ($affected <= 0) {
			throw $this->createDaoException ( 'Insert courseFile error.' );
		}
		return $this->getConnection ()->lastInsertId ();
	}
	
	public function deleteCourseFileLink($userId, $fileId, $targetId){
		$sql = "DELETE FROM {$this->table} WHERE userId = ? and fileId = ? and targetId = ?";
		return $this->getConnection()->executeUpdate($sql, array($userId, $fileId, $targetId));
	}
}