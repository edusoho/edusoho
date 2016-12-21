<?php


namespace Biz\Classroom\Dao\Impl;


use Biz\Classroom\Dao\ClassroomMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomMemberDaoImpl extends GeneralDaoImpl implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('role'=>'delimiter', 'assistantIds' => 'json', 'teacherIds' => 'json', 'service' => 'json'),
            'orderbys'   => array('name', 'createdTime'),
            'conditions' => array(
                'userId = :userId',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'role LIKE :role',
                'role IN (:roles)',
                'userId IN ( :userIds)',
                'createdTime >= :startTimeGreaterThan',
                'createdTime >= :createdTime_GE',
                'createdTime < :startTimeLessThan'
            )
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
        $sql   = "SELECT * FROM {$this->table} WHERE userId = {$userId} AND classroomId IN ({$marks});";

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

    public function countMobileVerifiedMembersByClassroomId($classroomId, $userLocked = 0)
    {
        $sql = "SELECT COUNT(m.id) FROM {$this->table}  m ";
        $sql .= " JOIN  `user` As c ON m.classroomId = ?";

        if ($userLocked) {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' AND c.locked != 1 AND m.locked != 1";
        } else {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' ";
        }

        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function findByClassroomIdAndRole($classroomId, $role, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $role = '%|'.$role.'|%';
        $sql  = "SELECT * FROM {$this->table} WHERE classroomId = ? AND role LIKE ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($classroomId, $role));
    }

    public function findMemberIdsByClassroomId($classroomId)
    {
        $sql = "SELECT userId FROM {$this->table} WHERE classroomId = ?";
        return $this->db()->executeQuery($sql, array($classroomId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ?";
        return $this->db()->executeQuery($sql, array($userId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    protected function _createQueryBuilder($conditions)
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

        return parent::_createQueryBuilder($conditions);
    }

}