<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseMemberDao;

class CourseMemberDaoImpl extends BaseDao implements CourseMemberDao
{
    protected $table = 'course_member';

    public function getMember($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $courseId));
    }

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId, $role));
    }

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND role = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId, $role));
    }

    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? AND isLearned = ? 
            ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId, $role, $isLearned));
    }

    public function findMemberCountByCourseIdAndRole($courseId, $role)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND role = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $role));
    }

    public function getMembersCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND role = ? AND isLearned = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $role, $isLearned));
    }

    public function findMembersByRole($role, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($role));
    }

    public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course member error.');
        }
        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function updateMember($id, $member)
    {
        $this->getConnection()->update($this->table, $member, array('id' => $id));
        return $this->getMember($id);
    }

    public function deleteMember($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteMembersByCourseId($courseId)
    {
        return $this->getConnection()->delete($this->table, array('courseId' => $courseId));
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getConnection()->delete($this->table, array('userId' => $userId, 'courseId' => $courseId));
    }
}