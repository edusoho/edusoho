<?php
namespace Topxia\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classroom\Dao\ClassroomMemberDao;
use Topxia\Service\Classroom\Dao\ClassroomDao;

class ClassroomMemberDaoImpl extends BaseDao implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function getMemberByClassIdAndUserId($classId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND classId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $classId)) ? : null;
    }


}