<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMemberDao;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;
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

        throw new \Exception('已经废弃，即将删除');
        return $this->db()->fetchColumn($sql, array($userId, $type, $isLearned, $role));
    }

    public function getMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        throw new \Exception('已经废弃，即将删除');
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE  userId = ? AND role = ? AND isLearned = ?";
        return $this->db()->fetchColumn($sql, array($userId, $role, $isLearned));
    }


    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit)
    {
        throw new \Exception('已经废弃，即将删除');
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT m.* FROM {$this->table()} m ";
        $sql .= ' JOIN  c2_course AS c ON m.userId = ? ';
        $sql .= " AND c.type =  ? AND m.courseId = c.id AND m.isLearned = ? AND m.role = ?";
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $type, $isLearned, $role));
    }


    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        throw new \Exception('已经废弃，即将删除');
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? AND isLearned = ?  ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, array($userId, $role, $isLearned));
    }

    //------------
    public function searchMemberFetchCourse($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_buildQueryBuilder($conditions)->select('m.*');
        if (!empty($orderBy)) {
            $builder = $builder->orderBy($orderBy[0], $orderBy[1]);
        }
        if ($start and $limit) {
            $builder = $builder->setFirstResult($start)->setMaxResults($limit);
        }
        return $builder->execute()->fetchAll() ?: array();
    }

    public function countMemberFetchCourse($conditions)
    {
        return $this->_buildQueryBuilder($conditions)->select(COUNT('m.courseId'))->execute()->fetchColumn(0);
    }

    protected function _buildQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || $value === null) {
                return false;
            }
            return true;
        });

        $builder = new DynamicQueryBuilder($this->db(), $conditions);
        $builder->from($this->table(), 'm')
            ->join('m', 'c2_course', 'c', ' m.courseId = c.id ')
            ->andWhere('m.isLearned = :isLearned')
            ->andWhere('m.userId = :userId')
            ->andWhere('m.role = :role')
            ->andWhere('m.courseId = :courseId')
            ->andWhere('m.joinedType =:joinedType')
            ->andWhere('m.noteNum > :noteNumGreaterThan')
            ->andWhere('c.type = :type')
            ->andWhere('c.parentId = parentId');
        return $builder;
    }

    //-----------------
    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int)$start;
        $limit = (int)$limit;

    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys'   => array('createdTime'),
            'conditions' => array(
                'userId = :userId',
                'courseId = :courseId',
                'isLearned = :isLearned',
                'joinedType = :joinedType',
                'role = :role',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
                'courseId IN (:courseIds)',
                'userId IN (:userIds)',
                'learnedNum >= :learnedNumGreaterThan',
                'learnedNum < :learnedNumLessThan',
            )
        );
    }
}
