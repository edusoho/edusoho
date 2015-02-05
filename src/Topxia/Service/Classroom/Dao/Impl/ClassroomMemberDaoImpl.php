<?php
namespace Topxia\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classroom\Dao\ClassroomMemberDao;
use Topxia\Service\Classroom\Dao\ClassroomDao;

class ClassroomMemberDaoImpl extends BaseDao implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function getMember($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
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

    public function findMemberCountByClassroomIdAndRole($classroomId, $role)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  classId = ? AND role = ?";
        return $this->getConnection()->fetchColumn($sql, array($classroomId, $role));
    }
    
    public function searchMemberCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();         
    }

    public function getMemberByClassroomIdAndUserId($classroomId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND classId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $classroomId)) ? : null;
    }

    public function deleteMember($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteMemberByClassroomIdAndUserId($classroomId, $userId)
    {
        return $this->getConnection()->delete($this->table, array('classId' => $classroomId,'userId' => $userId));
    }
    
    private function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'classroom_member')
            ->andWhere('userId = :userId')
            ->andWhere('classId = :classId')
            ->andWhere('noteNum > :noteNumGreaterThan')
            ->andWhere('role = :role')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan');

        // if (isset($conditions['courseIds'])) {
        //     $courseIds = array();
        //     foreach ($conditions['courseIds'] as $courseId) {
        //         if (ctype_digit($courseId)) {
        //             $courseIds[] = $courseId;
        //         }
        //     }
        //     if ($courseIds) {
        //         $courseIds = join(',', $courseIds);
        //         $builder->andStaticWhere("courseId IN ($courseIds)");
        //     }
        // }

        return $builder;
    }

}