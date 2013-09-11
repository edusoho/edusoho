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

    public function findMemberCountByUserIdAndRole($userId, $role)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND role = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $role));
    }

    public function findMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND role = ? AND isLearned = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $role, $isLearned));
    }

    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? AND isLearned = ? 
            ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId, $role, $isLearned));
    }

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND role = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId, $role));
    }

    public function findMemberCountByCourseIdAndRole($courseId, $role)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND role = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $role));
    }

    public function searchMemberCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchMember($conditions, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'ASC');
        return $builder->execute()->fetchAll() ? : array(); 
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
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE userId AND courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($userId, $courseId));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_member')
            ->andWhere('userId = :userId')
            ->andWhere('courseId = :courseId')
            ->andWhere('noteNum >= :noteNumGreaterThan')
            ->andWhere('role = :role')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan');
    }

}