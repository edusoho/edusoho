<?php
namespace Topxia\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classroom\Dao\ClassroomMemberDao;

class ClassroomMemberDaoImpl extends BaseDao implements ClassroomMemberDao
{
	protected $table = 'classroom_member';

	public function getMember($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

	public function findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,$role)
	{
        $sql = "SELECT * FROM {$this->table} where classId=? and userId=? and role=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($classroomId,$studentId,$role)) ? : null;
    
	}

	public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert classroom member error.');
        }
        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function getClassroomStudentCount($classroomId)
    {
        $sql = "SELECT count(*) FROM {$this->table} WHERE classId = ? LIMIT 1";
        return $this->getConnection()->fetchColumn($sql, array($classroomId)) ? : null;
    }
}

