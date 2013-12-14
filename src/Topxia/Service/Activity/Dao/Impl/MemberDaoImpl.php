<?php
namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\MemberDao;

class MemberDaoImpl extends BaseDao implements MemberDao
{

	protected $table = 'activity_member';

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
            ->orderBy("createdTime", "DESC")
            ->setFirstResult($start)
            ->setMaxResults($limit);
        
        return $builder->execute()->fetchAll() ? : array(); 
    }


    public function findMembersByIds(array $actIds,$userId)
    {
        if(empty($actIds)){ return array(); }
        $marks = str_repeat('?,', count($actIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE userId= $userId  and  activityId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $actIds);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'activity_member')
            ->andWhere('userId = :userId')
            ->andWhere('activityId = :activityId')
            ->andWhere('approvalStatus = :approvalStatus');
    }

    public function getMemberCountByUserId($userId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId));
    }

    public function findMembersByUserId($userId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function getMember($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getMemberByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND activityId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $activityId));
    }

    public function findMembersByUserIdAndRole($userId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function findMembersByActivityId($activityId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE activityId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($activityId));
    }

    public function findMembersByUserIdAndRoleAndIsLearned($userId, $isLearned, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ?  AND isLearned = ? 
            ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId, $isLearned));
    }

    public function findMemberCountByActivityIdAndRole($activityId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  activityId = ?";
        return $this->getConnection()->fetchColumn($sql, array($activityId));
    }

    public function getMembersCountByUserIdAndRoleAndIsLearned($userId, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND isLearned = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $role, $isLearned));
    }

    public function findMembersByRole($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($role));
    }

    public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert activity member error.');
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

    public function deleteMembersByActivityId($activityId)
    {
        return $this->getConnection()->delete($this->table, array('activityId' => $activityId));
    }

    public function deleteMemberByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getConnection()->delete($this->table, array('userId' => $userId, 'activityId' => $activityId));
    }
    
}