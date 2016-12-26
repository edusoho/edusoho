<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;

class CourseMemberDaoImpl extends GeneralDaoImpl implements CourseMemberDao
{
    protected $table = 'course_member';

    public function getByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $userId)) ?: null;
    }

    public function findStudentsByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and role = 'student'";
        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function findTeachersByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and role = 'teacher'";
        return $this->db()->fetchAll($sql, array($courseId));
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

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? AND userId = ? AND isLearned = 1";
        return $this->db()->fetchAll($sql, array($courseId, $userId));
    }

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

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int)$start;
        $limit = (int)$limit;
    }

    public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $userId)) ?: null;
    }

    public function findMembersByCourseIds($courseIds)
    {
        // TODO: Implement findMembersByCourseIds() method.
    }

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        // TODO: Implement findMembersByUserIdAndRole() method.
    }

    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        // TODO: Implement findMembersNotInClassroomByUserIdAndRole() method.
    }

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        // TODO: Implement findMemberCountByUserIdAndRole() method.
    }

    public function findMemberCountNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        // TODO: Implement findMemberCountNotInClassroomByUserIdAndRole() method.
    }

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        if ($role == 'student') {
            return $this->findStudentsByCourseId($courseId, $start, $limit);
        }

        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? AND role = ? ORDER BY seq, createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($courseId, $role));
    }

    public function findMemberCountByCourseIdAndRole($courseId, $role)
    {
        // TODO: Implement findMemberCountByCourseIdAndRole() method.
    }

    public function findMembersByUserIdAndJoinType($userId, $joinedType)
    {
        // TODO: Implement findMembersByUserIdAndJoinType() method.
    }

    public function searchMemberIds($conditions, $orderBy, $start, $limit)
    {
        // TODO: Implement searchMemberIds() method.
    }

    public function updateMembers($conditions, $updateFields)
    {
        // TODO: Implement updateMembers() method.
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        // TODO: Implement deleteMemberByCourseIdAndRole() method.
    }

    public function findCourseMembersByUserId($userId)
    {
        // TODO: Implement findCourseMembersByUserId() method.
    }

    public function deleteMembersByCourseId($courseId)
    {
        // TODO: Implement deleteMembersByCourseId() method.
    }

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds)
    {
        // TODO: Implement findCoursesByStudentIdAndCourseIds() method.
    }

    public function findMemberUserIdsByCourseId($courseId)
    {
        // TODO: Implement findMemberUserIdsByCourseId() method.
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
                'learnedNum < :learnedNumLessThan'
            )
        );
    }
}
