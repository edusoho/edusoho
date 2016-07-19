<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomMemberDao;

class ClassroomMemberDaoImpl extends BaseDao implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function getTable()
    {
        return $this->table;
    }

    public function getMember($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert classroom member error.');
        }

        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function getClassroomStudentCount($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:role:student:count", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT count(*) FROM {$that->getTable()} WHERE classroomId = ? AND role LIKE '%|student|%' LIMIT 1";
            return $that->getConnection()->fetchColumn($sql, array($classroomId));
        }

        );
    }

    public function getClassroomAuditorCount($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:role:auditor:count", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT count(*) FROM {$that->getTable()} WHERE classroomId = ? AND role LIKE '%|auditor|%' LIMIT 1";
            return $that->getConnection()->fetchColumn($sql, array($classroomId));
        }

        );
    }

    public function updateMember($id, $member)
    {
        $this->getConnection()->update($this->table, $member, array('id' => $id));
        $this->clearCached();
        return $this->getMember($id);
    }

    public function findAssistants($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:role:auditor", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE classroomId = ? AND role LIKE ('%|assistant|%')";
            return $that->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
        }

        );
    }

    public function findTeachers($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:role:teacher", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE classroomId = ? AND role LIKE ('%|teacher|%')";
            return $that->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
        }

        );
    }

    public function findMembersByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        if (empty($classroomIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE userId = {$userId} AND classroomId IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $classroomIds) ?: array();
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
        return $builder->execute()->fetchAll() ?: array();
    }

    public function getMemberByClassroomIdAndUserId($classroomId, $userId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:userId:{$userId}", $classroomId, $userId, function ($classroomId, $userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND classroomId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $classroomId)) ?: null;
        }

        );
    }

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId IN ({$marks});";

        $userIds = array_merge(array($classroomId), $userIds);

        return $this->getConnection()->fetchAll($sql, $userIds) ?: array();
    }

    public function deleteMember($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteMemberByClassroomIdAndUserId($classroomId, $userId)
    {
        $result = $this->getConnection()->delete($this->table, array('classroomId' => $classroomId, 'userId' => $userId));
        $this->clearCached();
        return $result;
    }

    public function findMobileVerifiedMemberCountByClassroomId($classroomId, $locked = 0)
    {
        $sql = "SELECT COUNT(m.id) FROM {$this->table}  m ";
        $sql .= " JOIN  `user` As c ON m.classroomId = ?";

        if ($locked) {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' AND c.locked != 1 AND m.locked != 1";
        } else {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' ";
        }

        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function findMembersByClassroomIdAndRole($classroomId, $role, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $role = '%|'.$role.'|%';
        $sql  = "SELECT * FROM {$this->table} WHERE classroomId = ? AND role LIKE ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($classroomId, $role));
    }

    public function findMemberUserIdsByClassroomId($classroomId)
    {
        $sql = "SELECT userId FROM {$this->table} WHERE classroomId = ?";
        return $this->getConnection()->executeQuery($sql, array($classroomId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['role'])) {
            $conditions['role'] = "%{$conditions['role']}%";
        }

        if (isset($conditions['roles'])) {
            $roles = "";

            foreach ($conditions['roles'] as $role) {
                $roles .= "|".$role;
            }

            $roles = $roles."|";

            foreach ($conditions['roles'] as $key => $role) {
                $conditions['roles'][$key] = "|".$role."|";
            }

            $conditions['roles'][] = $roles;
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'classroom_member')
            ->andWhere('userId = :userId')
            ->andWhere('classroomId = :classroomId')
            ->andWhere('noteNum > :noteNumGreaterThan')
            ->andWhere('role LIKE :role')
            ->andWhere('role IN (:roles)')
            ->andWhere('userId IN ( :userIds)')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime >= :createdTime_GE')
            ->andWhere('createdTime < :startTimeLessThan');

        return $builder;
    }
}
