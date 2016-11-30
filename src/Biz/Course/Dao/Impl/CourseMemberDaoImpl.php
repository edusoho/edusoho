<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseMemberDaoImpl extends GeneralDaoImpl implements CourseMemberDao
{
	protected $table = 'course_member';

	public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $userId)) ?: null;
    }

    public function declares()
    {

    }
}