<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseDao;
use Topxia\Service\Course\Dao\CourseMemberDao;

class CourseMemberDaoImpl extends BaseDao implements CourseMemberDao
{
    protected $table = 'course_member';

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
            throw $this->createDaoException('Insert course member error.');
        }

        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:userId:{$userId}", $courseId, $userId, function ($courseId, $userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? and userId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($courseId, $userId)) ?: null;
        }

        );
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:userId:{$userId}:isLearned:1", $courseId, $userId, function ($courseId, $userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND userId = ? AND isLearned = 1";
            return $that->getConnection()->fetchAll($sql, array($courseId, $userId));
        }

        );
    }

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLENAME.' AS c ON m.userId = ? ';
        $sql .= " AND m.role =  ? AND m.courseId = c.id ";

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $role));
    }

    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLENAME.' AS c ON m.userId = ? ';
        $sql .= " AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0";

        if ($onlyPublished) {
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $role));
    }

    public function getMembersByCourseIds($courseIds)
    {
        $marks         = str_repeat('?,', count($courseIds) - 1).'?';
        $sql           = "SELECT * FROM `course_member` WHERE courseId IN ({$marks})";
        $courseMembers = $this->getConnection()->fetchAll($sql, $courseIds);
        return $courseMembers;
    }

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:role:{$role}:onlyPublished:{$onlyPublished}:count", $userId, $role, $onlyPublished, function ($userId, $role, $onlyPublished) use ($that) {
            $sql = "SELECT COUNT( m.courseId ) FROM {$that->getTable()} m ";
            $sql .= " JOIN  ".CourseDao::TABLENAME." AS c ON m.userId = ? ";
            $sql .= " AND m.role =  ? AND m.courseId = c.id ";

            if ($onlyPublished) {
                $sql .= " AND c.status = 'published' ";
            }

            return $that->getConnection()->fetchColumn($sql, array($userId, $role));
        }

        );
    }

    public function findMemberCountNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:role:{$role}:onlyPublished:{$onlyPublished}:parentId:0:count", $userId, $role, $onlyPublished, function ($userId, $role, $onlyPublished) use ($that) {
            $sql = "SELECT COUNT( m.courseId ) FROM {$that->getTable()} m ";
            $sql .= " JOIN  ".CourseDao::TABLENAME." AS c ON m.userId = ? ";
            $sql .= " AND m.role =  ? AND m.courseId = c.id AND c.parentId = 0";

            if ($onlyPublished) {
                $sql .= " AND c.status = 'published' ";
            }

            return $that->getConnection()->fetchColumn($sql, array($userId, $role));
        }

        );
    }

    public function findMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:role:{$role}:type:{$type}:isLearned:{$isLearned}", $userId, $role, $type, $isLearned, function ($userId, $role, $type, $isLearned) use ($that) {
            $sql = "SELECT COUNT( m.courseId ) FROM {$that->getTable()} m ";
            $sql .= " JOIN  ".CourseDao::TABLENAME." AS c ON m.userId = ? ";
            $sql .= " AND c.type =  ? AND m.courseId = c.id  AND m.isLearned = ? AND m.role = ?";

            return $that->getConnection()->fetchColumn($sql, array($userId, $type, $isLearned, $role));
        }

        );
    }

    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $sql = "SELECT m.* FROM {$this->table} m ";
        $sql .= ' JOIN  '.CourseDao::TABLENAME.' AS c ON m.userId = ? ';
        $sql .= " AND c.type =  ? AND m.courseId = c.id AND m.isLearned = ? AND m.role = ?";
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $type, $isLearned, $role));
    }

    public function findAllMemberByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:role:{$role}:onlyPublished:{$onlyPublished}", $userId, $role, $onlyPublished, function ($userId, $role, $onlyPublished) use ($that) {
            $sql = "SELECT m.* FROM {$that->getTable()} m ";
            $sql .= ' JOIN  '.CourseDao::TABLENAME.' AS c ON m.userId = ? ';
            $sql .= " AND m.role =  ? AND m.courseId = c.id ";

            if ($onlyPublished) {
                $sql .= " AND c.status = 'published' ";
            }

            // $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

            return $that->getConnection()->fetchAll($sql, array($userId, $role));
        }

        );
    }

    public function findMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:role:{$role}:isLearned:{$isLearned}:count", $userId, $role, $isLearned, function ($userId, $role, $isLearned) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE  userId = ? AND role = ? AND isLearned = ?";
            return $that->getConnection()->fetchColumn($sql, array($userId, $role, $isLearned));
        }

        );
    }

    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = ? AND isLearned = ?
            ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId, $role, $isLearned));
    }

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        if ($role == 'student') {
            return $this->findStudentsByCourseId($courseId, $start, $limit);
        }

        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:role:{$role}:start:{$start}:limit:{$limit}", $courseId, $role, $start, $limit, function ($courseId, $role, $start, $limit) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND role = ? ORDER BY seq, createdTime DESC LIMIT {$start}, {$limit}";

            return $that->getConnection()->fetchAll($sql, array($courseId, $role));
        });
    }

    protected function findStudentsByCourseId($courseId, $start, $limit)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:role:student:start:{$start}:limit:{$limit}", $courseId, $start, $limit, function ($courseId, $start, $limit) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND role = 'student' ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

            return $that->getConnection()->fetchAll($sql, array($courseId));
        });
    }

    public function findMemberCountByCourseIdAndRole($courseId, $role)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:role:{$role}:count", $courseId, $role, function ($courseId, $role) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE  courseId = ? AND role = ?";
            return $that->getConnection()->fetchColumn($sql, array($courseId, $role));
        }

        );
    }

    public function findMobileVerifiedMemberCountByCourseId($courseId, $locked = 0)
    {
        $sql = "SELECT COUNT(m.id) FROM {$this->table}  m ";
        $sql .= " JOIN  `user` As c ON m.courseId = ?";

        if ($locked) {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' AND c.locked != 1 AND m.locked != 1 ";
        } else {
            $sql .= " AND m.userId = c.id AND c.verifiedMobile != ' ' ";
        }

        return $this->getConnection()->fetchColumn($sql, array($courseId));
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

    public function searchMember($conditions, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'ASC');
        return $builder->execute()->fetchAll() ?: array();
    }

    public function countMembersByStartTimeAndEndTime($startTime, $endTime)
    {
        $sql = "SELECT * FROM (SELECT courseId, count(userId) AS co,role FROM {$this->table} WHERE createdTime <  ? AND createdTime > ? AND role='student' AND classroomId = 0  GROUP BY courseId) coursemembers ORDER BY coursemembers.co DESC LIMIT 0,5";
        return $this->getConnection()->fetchAll($sql, array($endTime, $startTime));
    }

    public function searchMemberIds($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions);

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

    public function updateMember($id, $member)
    {
        $this->getConnection()->update($this->table, $member, array('id' => $id));
        $this->clearCached();
        return $this->getMember($id);
    }

    public function updateMembers($conditions, $updateFields)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->update($this->table, $this->table);

        if ($updateFields) {
            foreach ($updateFields as $key => $value) {
                $builder->add('set', $key.' = '.$value, true);
            }
        }

        $builder->execute();
        $this->clearCached();

        return true;
    }

    public function deleteMember($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        $sql    = "DELETE FROM {$this->table} WHERE courseId = ? AND role= ?";
        $result = $this->getConnection()->executeUpdate($sql, array($courseId, $role));
        $this->clearCached();
        return $result;
    }

    public function deleteMembersByCourseId($courseId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE courseId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($courseId));
        $this->clearCached();
        return $result;
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE userId = ? AND courseId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($userId, $courseId));
        $this->clearCached();
        return $result;
    }

    public function findCourseMembersByUserId($userId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}", $userId, function ($userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND role = 'student' AND deadlineNotified=0 AND deadline>0 LIMIT 0,10";
            return $that->getConnection()->fetchAll($sql, array($userId));
        }

        );
    }

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE userId = ? AND role = 'student' AND courseId in ($marks)";
        return $this->getConnection()->fetchAll($sql, array_merge(array($studentId), $courseIds));
    }

    public function findMemberUserIdsByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT userId FROM {$that->getTable()} WHERE courseId = ?";
            return $that->getConnection()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
        }

        );
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_member')
            ->andWhere('userId = :userId')
            ->andWhere('courseId = :courseId')
            ->andWhere('isLearned = :isLearned')
            ->andWhere('noteNum > :noteNumGreaterThan')
            ->andWhere('role = :role')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan')
            ->andWhere('courseId IN (:courseIds)')
            ->andWhere('userId IN (:userIds)')
            ->andWhere('learnedNum >= :learnedNumGreaterThan')
            ->andWhere('learnedNum < :learnedNumLessThan')
            ->andWhere('classroomId = :classroomId');
        return $builder;
    }
}
