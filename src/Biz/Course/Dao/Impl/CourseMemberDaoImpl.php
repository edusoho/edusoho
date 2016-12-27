<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;

class CourseMemberDaoImpl extends GeneralDaoImpl implements CourseMemberDao
{
    protected $table = 'course_member';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ?";
        return $this->db()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ?";
        return $this->db()->executeQuery($sql, array($userId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findByCourseIds($courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table()} WHERE courseId IN ({$marks})";
        return $this->db()->fetchAll($sql, $courseIds);
    }

    public function getByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $userId)) ?: null;
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

    public function findByUserIdAndJoinType($userId, $joinedType)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE  userId = ? AND joinedType = ?";
        return $this->db()->fetchAll($sql, array($userId, $joinedType));
    }

    public function deleteByCourseIdAndRole($courseId, $role)
    {
        return $this->db()->delete($this->table(), array(array('courseId' => $courseId, 'role' => $role)));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array(array('courseId' => $courseId)));
    }

    public function findByUserIdAndCourseIds($studentId, $courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table()} WHERE userId = ? AND role = 'student' AND courseId in ($marks)";
        return $this->db()->fetchAll($sql, array_merge(array($studentId), $courseIds));
    }

    public function searchMemberFetchCourse($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_buildQueryBuilder($conditions)->select('m.*');
        if (!empty($orderBy)) {
            $builder = $builder->orderBy($orderBy[0], $orderBy[1]);
        }
        if ($start && $limit) {
            $builder = $builder->setFirstResult($start)->setMaxResults($limit);
        }
        return $builder->execute()->fetchAll() ?: array();
    }

    public function countMemberFetchCourse($conditions)
    {
        return $this->_buildQueryBuilder($conditions)->select(COUNT('m.courseId'))->execute()->fetchColumn(0);
    }


//    public function searchMemberFetchUser($conditions, $orderBy, $start, $limit)
//    {
//        $builder = $this->_buildQueryBuilder($conditions, $join)->select('*');
//        if (!empty($orderBy)) {
//            $builder = $builder->orderBy($orderBy[0], $orderBy[1]);
//        }
//        if ($start && $limit) {
//            $builder = $builder->setFirstResult($start)->setMaxResults($limit);
//        }
//        return $builder->execute()->fetchAll() ?: array();
//    }
//
//    public function countMemberFetchUser($conditions)
//    {
//        return $this->_buildQueryBuilder($conditions, $join)->select(COUNT('m.courseId'))->execute()->fetchColumn(0);
//    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('courseId, COUNT(id) AS count')
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


    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  c2_course AS c ON m.userId = ? ';
        $sql .= " AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0";

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId, $role));
    }

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table()} m ";
        $sql .= " JOIN  c2_course AS c ON m.userId = ? ";
        $sql .= " AND m.role =  ? AND m.courseId = c.id ";

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        return $this->db()->fetchColumn($sql, array($userId, $role));
    }

    public function findMemberCountNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->getTable()} m ";
        $sql .= " JOIN  c2_course AS c ON m.userId = ? ";
        $sql .= " AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0";

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        return $this->db()->fetchColumn($sql, array($userId, $role));
    }


    public function searchMemberIds($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions);

        if (isset($conditions['unique'])) {
            $builder->select('*');
            $builder->orderBy($orderBy[0], $orderBy[1]);
            $builder->from('('.$builder->getSQL().')', $this->getTable());
            $builder->select('DISTINCT userId');
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
        $builder = $this->_createQueryBuilder($conditions)
            ->update($this->table(), $this->table());

        if ($updateFields) {
            foreach ($updateFields as $key => $value) {
                $builder->add('set', $key.' = '.$value, true);
            }
        }
        $builder->execute();
        return true;
    }


    protected function _buildQueryBuilder($conditions, $join)
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


    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
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
                'deadline >= :deadlineGreaterThan'
            )
        );
    }
}
