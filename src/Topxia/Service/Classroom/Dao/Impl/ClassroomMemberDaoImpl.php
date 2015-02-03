<?php
namespace Topxia\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classroom\Dao\ClassroomMemberDao;

class ClassroomMemberDaoImpl extends BaseDao implements ClassroomMemberDao
{
	protected $table = 'classroom_member';

	public function findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,$role)
	{
        $sql = "SELECT * FROM {$this->table} where classId=? and userId=? and role=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($classroomId,$studentId,$role)) ? : null;
    
	}
}