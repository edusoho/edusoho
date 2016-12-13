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

    public function findStudentsByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and role = 'student'";
        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function getMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table()} m ";
        $sql .= " JOIN  c2_course AS c ON m.userId = ? ";
        $sql .= " AND c.type =  ? AND m.courseId = c.id  AND m.isLearned = ? AND m.role = ?";

        return $this->db()->fetchColumn($sql, array($userId, $type, $isLearned, $role));
    }

    public function getMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE  userId = ? AND role = ? AND isLearned = ?";
        return $this->db()->fetchColumn($sql, array($userId, $role, $isLearned));
    }


    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT m.* FROM {$this->table()} m ";
        $sql .= ' JOIN  c2_course AS c ON m.userId = ? ';
        $sql .= " AND c.type =  ? AND m.courseId = c.id AND m.isLearned = ? AND m.role = ?";
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $type, $isLearned, $role));
    }


    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? AND isLearned = ?  ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, array($userId, $role, $isLearned));
    }


    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int)$start;
        $limit = (int)$limit;

    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'courseId = :courseId',
                'role = :role'
            )
        );
    }
}
