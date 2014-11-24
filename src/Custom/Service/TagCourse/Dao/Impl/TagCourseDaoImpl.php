<?php
namespace Custom\Service\TagCourse\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\TagCourse\Dao\TagCourseDao;

class TagCourseDaoImpl extends BaseDao implements TagCourseDao{
	 protected $table = 'course';
	public function getCourseStudentCountByTagIdAndCourseStatus($tagId,$status){
		$sql ="SELECT  sum(studentNum)  as studentNums FROM {$this->table} WHERE 1=1 ";
		if(!empty($status)){
			$sql.=" AND status='$status' ";
		}
		if(!empty($tagId)){
			   $sql .= " AND tags LIKE '%|$tagId|%'";
		}
		return $this->getConnection()->fetchAll($sql);

	}
}