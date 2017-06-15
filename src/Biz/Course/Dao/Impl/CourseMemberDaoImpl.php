<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;

class CourseMemberDaoImpl extends GeneralDaoImpl implements CourseMemberDao
{
    protected $table = 'course_member';
    protected $alias = 'm';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByCourseId($courseId)
    {
        return $this->findByFields(array(
            'courseId' => $courseId,
        ));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array(
            'userId' => $userId,
        ));
    }

    public function findByCourseIds($courseIds)
    {
        return $this->findInField('courseId', $courseIds);
    }

    public function getByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getByFields(array(
            'courseId' => $courseId,
            'userId' => $userId,
        ));
    }

    public function findLearnedByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? AND userId = ? AND isLearned = 1";

        return $this->db()->fetchAll($sql, array($courseId, $userId));
    }

    public function findByCourseIdAndRole($courseId, $role)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? AND role = ? ORDER BY seq, createdTime DESC";

        return $this->db()->fetchAll($sql, array($courseId, $role));
    }

    public function findByCourseSetIdAndRole($courseSetId, $role)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseSetId = ? AND role = ? ORDER BY seq, createdTime DESC";

        return $this->db()->fetchAll($sql, array($courseSetId, $role));
    }

    public function findByUserIdAndJoinType($userId, $joinedType)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE  userId = ? AND joinedType = ?";

        return $this->db()->fetchAll($sql, array($userId, $joinedType));
    }

    public function deleteByCourseIdAndRole($courseId, $role)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId, 'role' => $role));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function findByUserIdAndCourseIds($studentId, $courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND role = 'student' AND courseId in ($marks)";

        return $this->db()->fetchAll($sql, array_merge(array($studentId), $courseIds));
    }

    public function countLearningMembers($conditions)
    {
        $sql = "SELECT COUNT(m.id) FROM {$this->table()} m ";
        $sql .= ' INNER JOIN course_v8 c ON m.courseId = c.id ';
        $sql .= ' WHERE ';

        list($sql, $params) = $this->applySqlParams($conditions, $sql);

        $sql .= '(m.learnedNum < c.publishedTaskNum) ';

        return $this->db()->fetchColumn($sql, $params);
    }

    public function findLearningMembers($conditions, $start, $limit)
    {
        $sql = "SELECT m.* FROM {$this->table()} m ";
        $sql .= ' INNER JOIN course_v8 c ON m.courseId = c.id ';
        $sql .= ' WHERE ';

        list($sql, $params) = $this->applySqlParams($conditions, $sql);

        $sql .= '(m.learnedNum < c.publishedTaskNum) ';
        $sql .= "ORDER BY createdTime DESC LIMIT {$start}, {$limit} ";

        return $this->db()->fetchAll($sql, $params) ?: array();
    }

    public function countLearnedMembers($conditions)
    {
        $sql = "SELECT COUNT(m.id) FROM {$this->table()} m ";
        $sql .= ' INNER JOIN course_v8 c ON m.courseId = c.id ';
        $sql .= ' WHERE ';

        list($sql, $params) = $this->applySqlParams($conditions, $sql);
        $sql .= 'm.learnedNum >= c.publishedTaskNum ';

        return $this->db()->fetchColumn($sql, $params);
    }

    public function findLearnedMembers($conditions, $start, $limit)
    {
        $sql = "SELECT m.* FROM {$this->table()} m ";
        $sql .= ' INNER JOIN course_v8 c ON m.courseId = c.id ';
        $sql .= ' WHERE ';
        list($sql, $params) = $this->applySqlParams($conditions, $sql);

        $sql .= 'm.learnedNum >= c.publishedTaskNum ';
        $sql .= "ORDER BY createdTime DESC LIMIT {$start}, {$limit} ";

        return $this->db()->fetchAll($sql, $params) ?: array();
    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("{$groupBy}, COUNT(id) AS count")
            ->groupBy($groupBy)
            ->orderBy('count', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function findByUserIdAndRole($userId, $role)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND role =  ?";

        return $this->db()->fetchAll($sql, array($userId, $role));
    }

    /**
     * @param  $userId
     * @param  $courseSetId
     * @param  $role
     *
     * @return array
     */
    public function findByUserIdAndCourseSetIdAndRole($userId, $courseSetId, $role)
    {
        return $this->findByFields(array(
            'userId' => $userId,
            'courseSetId' => $courseSetId,
            'role' => $role,
        ));
    }

    public function findByConditionsGroupByUserId($conditions, $orderBy, $offset, $limit)
    {
        $fields = array_keys($conditions);
        array_walk($fields, function (&$value) {
            $value = $value.' = ? ';
        });

        $wherePart = '('.implode(') '.'AND'.' (', $fields).')';

        $declares = $this->declares();
        $selectFields = array_merge(array('id'), $declares['orderbys']);
        array_walk($selectFields, function (&$value) {
            $value = 'MAX('.$value.') AS '.$value;
        });

        $selectList = implode(',', $selectFields);

        $sql = "SELECT {$selectList} FROM {$this->table} WHERE {$wherePart} GROUP BY UserId";

        return $this->db()->fetchAll($this->sql($sql, $orderBy, $offset, $limit), array_values($conditions));
    }

    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  course_v8 AS c ON m.userId = ? ';
        $sql .= ' AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0';

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $role));
    }

    public function searchMemberIds($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions);

        if (isset($conditions['unique'])) {
            $builder->select('userId');
            $builder->orderBy($orderBy[0], $orderBy[1]);
            $builder->from('('.$builder->getSQL().')', $this->table());
            //when we use distinct in strict mode, it's not allowed to order by field that is not in select part,
            //so we use a sub query, and reset result field here.
            $builder->select('distinct userId');
            $builder->resetQueryPart('where');
            $builder->resetQueryPart('orderBy');
        } else {
            $builder->select('userId');
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        $builder->setFirstResult($start);
        $builder->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function updateMembers($conditions, $updateFields)
    {
        return $this->db()->update($this->table, $updateFields, $conditions);
    }

    public function countThreadsByCourseIdAndUserId($courseId, $userId, $type = 'discuss')
    {
        $sql = "SELECT count(id) FROM course_thread WHERE type='{$type}' AND courseId = ? AND userId = ?";

        return $this->db()->fetchColumn($sql, array($courseId, $userId));
    }

    public function countActivitiesByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT count(distinct(activityId)) FROM course_task_result WHERE courseId = ? AND userId = ?';

        return $this->db()->fetchColumn($sql, array($courseId, $userId));
    }

    public function countPostsByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT count(id) FROM course_thread_post WHERE userId = ? and threadId IN (SELECT id FROM course_thread WHERE courseId = ? AND type='discussion')";

        return $this->db()->fetchColumn($sql, array($userId, $courseId));
    }

    public function countMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= ' AND m.role = ? AND c.type =  ? AND m.isLearned = ? AND m.courseId = c.id  AND c.parentId = 0';

        return $this->db()->fetchColumn($sql, array($userId, $role, $type, $isLearned));
    }

    public function countMemberNotInClassroomByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= ' AND m.role = ? AND m.isLearned = ? AND m.courseId = c.id  AND c.parentId = 0';

        return $this->db()->fetchColumn($sql, array($userId, $role, $isLearned));
    }

    public function countMemberNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= ' AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0';

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        return $this->db()->fetchColumn($sql, array($userId, $role));
    }

    public function findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned(
        $userId,
        $role,
        $type,
        $isLearned,
        $start,
        $limit
    ) {
        $sql = "SELECT m.* FROM {$this->table} m";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= 'AND m.role = ? AND c.type = ?  AND m.isLearned = ? AND m.courseId = c.id AND c.parentId = 0';
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $role, $type, $isLearned));
    }

    public function findMembersNotInClassroomByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= 'AND m.role =  ? AND m.isLearned = ? AND m.courseId = c.id AND c.parentId = 0';

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $role, $isLearned));
    }

    public function countMemberByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= ' AND c.type =  ? AND m.courseId = c.id  AND m.isLearned = ? AND m.role = ?';

        return $this->db()->fetchColumn($sql, array($userId, $type, $isLearned, $role));
    }

    public function countMemberByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND role = ? AND isLearned = ?";

        return $this->db()->fetchColumn($sql, array($userId, $role, $isLearned));
    }

    public function findMembersNotInClassroomByUserIdAndRoleAndType(
        $userId,
        $role,
        $type,
        $start,
        $limit,
        $onlyPublished = true
    ) {
        $sql = "SELECT m.* FROM {$this->table} m ";

        $sql .= ' JOIN  '.CourseDao::TABLE_NAME.' AS c ON m.userId = ? ';
        $sql .= ' AND m.role =  ? AND c.type = ? AND m.courseId = c.id AND c.parentId = 0';

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $role, $type));
    }

    public function updateByClassroomIdAndUserId($classroomId, $userId, array $fields)
    {
        return $this->update(array(
            'classroomId' => $classroomId,
            'userId' => $userId,
        ), $fields);
    }

    public function updateByClassroomId($classroomId, array $fields)
    {
        return $this->update(array(
            'classroomId' => $classroomId,
        ), $fields);
    }

    protected function _buildJoinQueryBuilder($conditions, $joinConnections = '')
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || $value === null) {
                return false;
            }

            return true;
        });

        $builder = new DynamicQueryBuilder($this->db(), $conditions);
        $builder->from($this->table(), 'm')
            ->join('m', 'course_v8', 'c', 'm.courseId = c.id '.$joinConnections)
            ->andWhere('m.isLearned = :isLearned')
            ->andWhere('m.userId = :userId')
            ->andWhere('m.role = :role')
            ->andWhere('m.courseId = :courseId')
            ->andWhere('m.joinedType =:joinedType')
            ->andWhere('m.noteNum > :noteNumGreaterThan')
            ->andWhere('c.type = :type')
            ->andWhere('c.parentId = :parentId')
            ->andWhere('c.serializeMode =  :serializeMode')
            ->andWhere('c.serializeMode IN ( :serializeModes)');

        return $builder;
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array(
                'createdTime',
                'lastLearnTime',
                'classroomId',
                'id',
                'updatedTime',
                'lastViewTime',
                'seq',
            ),
            'conditions' => array(
                'id NOT IN (:excludeIds)',
                'userId = :userId',
                'courseSetId = :courseSetId',
                'courseId = :courseId',
                'isLearned = :isLearned',
                'joinedType = :joinedType',
                'role = :role',
                'isVisible = :isVisible',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
                'courseId IN (:courseIds)',
                'courseSetId IN (:courseSetIds)',
                'userId IN (:userIds)',
                'learnedNum >= :learnedNumGreaterThan',
                'learnedNum < :learnedNumLessThan',
                'deadline >= :deadlineGreaterThan',
                'lastViewTime >= :lastViewTime_GE',
                'lastLearnTime >= :lastLearnTimeGreaterThan',
                'updatedTime >= :updatedTime_GE',
                'finishedTime >= :finishedTime_GE',
                'finishedTime <= :finishedTime_LE',
            ),
        );
    }

    /**
     * @param  $conditions
     * @param  $sql
     *
     * @return array
     */
    protected function applySqlParams($conditions, $sql)
    {
        $params = array();
        $conditions = array_filter($conditions, function ($value) {
            return !empty($value);
        });
        foreach ($conditions as $key => $value) {
            $sql .= $key.' = ? AND ';
            array_push($params, $value);
        }

        return array($sql, $params);
    }
}
