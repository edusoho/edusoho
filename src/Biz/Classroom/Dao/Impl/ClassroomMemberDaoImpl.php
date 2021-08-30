<?php

namespace Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\ClassroomMemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ClassroomMemberDaoImpl extends AdvancedDaoImpl implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function searchMembersByClassroomId($classroomId, $conditions, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ?";
        list($sql, $conditions) = $this->filterSql($sql, $conditions);
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit};";

        return $this->db()->fetchAll($sql, array_merge([$classroomId], $conditions)) ?: [];
    }

    public function countMembersByClassroomId($classroomId, $conditions)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE classroomId = ?";
        list($sql, $conditions) = $this->filterSql($sql, $conditions);
        $sql .= ';';

        return $this->db()->fetchColumn($sql, array_merge([$classroomId], $conditions));
    }

    protected function filterSql($sql, $conditions)
    {
        $params = [];
        if (isset($conditions['role'])) {
            $params[] = $conditions['role'];
            $sql .= ' AND role LIKE ?';
        }
        if (isset($conditions['userIds'])) {
            $params[] = empty($conditions['userIds']) ? [-1] : $conditions['userIds'];
            $marks = str_repeat('?,', count($conditions['userIds']) - 1).'?';
            $sql .= " AND userId IN ({$marks})";
        }
        if (isset($conditions['in_validity'])) {
            $params[] = $conditions['in_validity']['deadline_GT'];
            $params[] = $conditions['in_validity']['deadline_EQ'];
            $sql .= " AND (deadline > ? OR deadline = ?)";
        }
        if (isset($conditions['out_validity'])) {
            $params[] = $conditions['out_validity']['deadline_LE'];
            $params[] = $conditions['out_validity']['deadline_GT'];
            $sql .= " AND deadline <= ? AND deadline > ?";
        }

        return [$sql, $params];
    }

    public function updateByClassroomIdAndRole($classroomId, $role, array $fields)
    {
        $conditions = [
            'classroomId' => $classroomId,
            'role' => $role,
        ];

        return $this->update($conditions, $fields);
    }

    public function findMembersByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        if (empty($classroomIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND classroomId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$userId], $classroomIds)) ?: [];
    }

    public function findMembersByUserId($userId)
    {
        return $this->findByFields(
            ['userId' => $userId]
        );
    }

    public function countStudents($classroomId)
    {
        $sql = "SELECT count(*) FROM {$this->table()} WHERE classroomId = ? AND role LIKE '%|student|%' LIMIT 1";

        return $this->db()->fetchColumn($sql, [$classroomId]);
    }

    public function countAuditors($classroomId)
    {
        $sql = "SELECT count(*) FROM {$this->table()} WHERE classroomId = ? AND role LIKE '%|auditor|%' LIMIT 1";

        return $this->db()->fetchColumn($sql, [$classroomId]);
    }

    public function findAssistantsByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND role LIKE ('%|assistant|%')";

        return $this->db()->fetchAll($sql, [$classroomId]) ?: [];
    }

    public function findTeachersByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND role LIKE ('%|teacher|%')";

        return $this->db()->fetchAll($sql, [$classroomId]) ?: [];
    }

    public function findByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        if (empty($classroomIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = {$userId} AND classroomId IN ({$marks});";

        return $this->db()->fetchAll($sql, $classroomIds) ?: [];
    }

    public function getByClassroomIdAndUserId($classroomId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND classroomId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$userId, $classroomId]) ?: null;
    }

    public function findByClassroomIdAndUserIds($classroomId, $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId IN ({$marks});";

        $userIds = array_merge([$classroomId], $userIds);

        return $this->db()->fetchAll($sql, $userIds) ?: [];
    }

    public function deleteByClassroomIdAndUserId($classroomId, $userId)
    {
        $result = $this->db()->delete($this->table, ['classroomId' => $classroomId, 'userId' => $userId]);

        return $result;
    }

    public function countMobileFilledMembersByClassroomId($classroomId, $userLocked = 0)
    {
        $sql = "SELECT COUNT(DISTINCT `mobile`) FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND `mobile` != '' and u.id in (SELECT userId FROM {$this->table} where classroomId = '{$classroomId}')";
        if ($userLocked) {
            $sql .= ' AND u.locked != 1';
        }

        return $this->db()->fetchColumn($sql, [$classroomId]);
    }

    public function findByClassroomIdAndRole($classroomId, $role, $start, $limit)
    {
        $role = '%|'.$role.'|%';
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND role LIKE ? ORDER BY createdTime DESC";
        $sql = $this->sql($sql, [], $start, $limit);

        return $this->db()->fetchAll($sql, [$classroomId, $role]);
    }

    public function findByUserId($userId)
    {
        return $this->findByFields([
            'userId' => $userId,
        ]);
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

        return $builder->execute()->fetchAll() ?: [];
    }

    public function searchSignStatisticsByClassroomId($classroomId, array $conditions, array $orderBys, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $userIds = $this->db()->fetchAll("SELECT userId FROM {$this->table} WHERE classroomId = ?", [$classroomId]);
        if (empty($userIds)) {
            return [];
        }

        $conditions['classroomId'] = empty($conditions['classroomId']) ? $classroomId : $conditions['classroomId'];
        $userIdsMarks = implode(',', array_column($userIds, 'userId'));

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$this->table}.*, 
                IF(m.signDays, m.signDays, 0) AS signDays, 
                IF(m.keepDays, m.keepDays, 0) AS keepDays,
                IF(m.lastSignTime, m.lastSignTime, 0) AS lastSignTime
            ")->leftJoin(
                $this->table,
                "(SELECT * FROM sign_user_statistics WHERE userId IN ({$userIdsMarks}) AND targetType = 'classroom_sign' AND targetId = {$classroomId})",
                'm',
                "m.userId = {$this->table}.userId"
            )->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys as $sort => $order) {
            if (in_array($sort, ['keepDays', 'signDays'])) {
                $builder->addOrderBy($sort, $order);
            }
        }

        return $builder->execute()->fetchAll();
    }

    public function findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange($classroomId, $role, $startTime, $endTime, $format = '%Y-%m-%d')
    {
        $sql = "SELECT count(*) as count, from_unixtime(createdTime, '{$format}') as date
                FROM {$this->table} WHERE `classroomId` = ? AND `role` LIKE ? AND createdTime > ? AND createdTime <= ? 
                GROUP BY date ORDER BY date ASC;";

        return $this->db()->fetchAll($sql, [$classroomId, "%|{$role}|%", $startTime, $endTime]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'role' => 'delimiter',
                'assistantIds' => 'json',
                'teacherIds' => 'json',
                'service' => 'json',
            ],
            'orderbys' => ['name', 'createdTime', 'updatedTime', 'id', 'deadline', 'learnedCompulsoryTaskNum', 'lastLearnTime'],
            'conditions' => [
                'userId = :userId',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'role LIKE :role',
                'role IN (:roles)',
                "{$this->table}.userId IN ( :userIds)",
                'createdTime >= :startTimeGreaterThan',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LTE',
                'createdTime < :createdTime_LT',
                'createdTime < :startTimeLessThan',
                'updatedTime >= :updatedTime_GE',
                'userId NOT IN ( :excludeUserIds )',
                'isFinished = :isFinished',
                'lastLearnTime >= :lastLearnTime_GTE',
                'lastLearnTime <= :lastLearnTime_LTE',
                'lastLearnTime > :lastLearnTime_GT',
                'lastLearnTime < :lastLearnTime_LT',
                'learnedCompulsoryTaskNum = :learnedCompulsoryTaskNum',
                'learnedCompulsoryTaskNum > :learnedCompulsoryTaskNum_GT',
                'learnedCompulsoryTaskNum < :learnedCompulsoryTaskNum_LT',
                'learnedCompulsoryTaskNum >= :learnedCompulsoryTaskNum_GTE',
                'learnedCompulsoryTaskNum <= :learnedCompulsoryTaskNum_LTE',
                'deadline <= :deadline_LE',
                'deadline >= :deadline_GE',
                'deadline > :deadline_GT',
            ],
        ];
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
