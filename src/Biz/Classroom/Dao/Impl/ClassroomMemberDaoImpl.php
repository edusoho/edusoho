<?php

namespace Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\ClassroomMemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ClassroomMemberDaoImpl extends AdvancedDaoImpl implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function updateByClassroomIdAndRole($classroomId, $role, array $fields)
    {
        $conditions = array(
            'classroomId' => $classroomId,
            'role' => $role,
        );

        return $this->update($conditions, $fields);
    }

    public function findMembersByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        if (empty($classroomIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND classroomId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge(array($userId), $classroomIds)) ?: array();
    }

    public function findMembersByUserId($userId)
    {
        return $this->findByFields(
            array('userId' => $userId)
        );
    }

    public function countStudents($classroomId)
    {
        $sql = "SELECT count(*) FROM {$this->table()} WHERE classroomId = ? AND role LIKE '%|student|%' LIMIT 1";

        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function countAuditors($classroomId)
    {
        $sql = "SELECT count(*) FROM {$this->table()} WHERE classroomId = ? AND role LIKE '%|auditor|%' LIMIT 1";

        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function findAssistantsByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND role LIKE ('%|assistant|%')";

        return $this->db()->fetchAll($sql, array($classroomId)) ?: array();
    }

    public function findTeachersByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND role LIKE ('%|teacher|%')";

        return $this->db()->fetchAll($sql, array($classroomId)) ?: array();
    }

    public function findByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        if (empty($classroomIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = {$userId} AND classroomId IN ({$marks});";

        return $this->db()->fetchAll($sql, $classroomIds) ?: array();
    }

    public function getByClassroomIdAndUserId($classroomId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND classroomId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId, $classroomId)) ?: null;
    }

    public function findByClassroomIdAndUserIds($classroomId, $userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId IN ({$marks});";

        $userIds = array_merge(array($classroomId), $userIds);

        return $this->db()->fetchAll($sql, $userIds) ?: array();
    }

    public function deleteByClassroomIdAndUserId($classroomId, $userId)
    {
        $result = $this->db()->delete($this->table, array('classroomId' => $classroomId, 'userId' => $userId));

        return $result;
    }

    public function countMobileFilledMembersByClassroomId($classroomId, $userLocked = 0)
    {
        $sql = "SELECT COUNT(DISTINCT `mobile`) FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND `mobile` != '' and u.id in (SELECT userId FROM {$this->table} where classroomId = '{$classroomId}')";
        if ($userLocked) {
            $sql .= ' AND u.locked != 1';
        }

        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function findByClassroomIdAndRole($classroomId, $role, $start, $limit)
    {
        $role = '%|'.$role.'|%';
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND role LIKE ? ORDER BY createdTime DESC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($classroomId, $role));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array(
            'userId' => $userId,
        ));
    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createQueryBuilder($conditions)
            ->select("{$groupBy}, COUNT(id) AS count")
            ->groupBy($groupBy)
            ->orderBy('count', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'role' => 'delimiter',
                'assistantIds' => 'json',
                'teacherIds' => 'json',
                'service' => 'json',
            ),
            'orderbys' => array('name', 'createdTime', 'updatedTime', 'id', 'deadline'),
            'conditions' => array(
                'userId = :userId',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'role LIKE :role',
                'role IN (:roles)',
                'userId IN ( :userIds)',
                'createdTime >= :startTimeGreaterThan',
                'createdTime >= :createdTime_GE',
                'createdTime < :startTimeLessThan',
                'updatedTime >= :updatedTime_GE',
                'userId NOT IN ( :excludeUserIds )',
            ),
        );
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['roles'])) {
            $roles = '';

            foreach ($conditions['roles'] as $role) {
                $roles .= '|'.$role;
            }

            $roles = $roles.'|';

            foreach ($conditions['roles'] as $key => $role) {
                $conditions['roles'][$key] = '|'.$role.'|';
            }

            $conditions['roles'][] = $roles;
        }

        return parent::createQueryBuilder($conditions);
    }
}
