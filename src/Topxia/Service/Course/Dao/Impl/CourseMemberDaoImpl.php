<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseMemberDao;
use Topxia\Service\Course\Dao\CourseDao;

class CourseMemberDaoImpl extends BaseDao implements CourseMemberDao
{
    protected $table = 'course_member';

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

    public function getMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $courseId)) ? : null;
    }

    public function findLearnedCoursesByCourseIdAndUserId($courseId,$userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND userId = ? AND isLearned = 1";
        return $this->getConnection()->fetchAll($sql, array($courseId, $userId));
    }

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true)
    {
        $this->filterStartLimit($start, $limit);

        $sql  = "SELECT m.* FROM {$this->table} m ";
        $sql.= ' JOIN  '. CourseDao::TABLENAME . ' AS c ON m.userId = ? ';
        $sql .= " AND m.role =  ? AND m.courseId = c.id ";
        if($onlyPublished){
            $sql .= " AND c.status = 'published' ";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $role));
    }

    public function getMembersByCourseIds($courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1) . '?';
        $sql = "SELECT * FROM `course_member` WHERE courseId IN ({$marks})";
        $courseMembers =  $this->getConnection()->fetchAll($sql, $courseIds);
        return $courseMembers;
    }

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql.= " JOIN  ". CourseDao::TABLENAME ." AS c ON m.userId = ? ";
        $sql.= " AND m.role =  ? AND m.courseId = c.id ";
        if($onlyPublished){
            $sql.= " AND c.status = 'published' ";
        }
        return $this->getConnection()->fetchColumn($sql,array($userId, $role));
    }

    public function findMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned)
    {
        $sql = "SELECT COUNT( m.courseId ) FROM {$this->table} m ";
        $sql.= " JOIN  ". CourseDao::TABLENAME ." AS c ON m.userId = ? ";
        $sql.= " AND c.type =  ? AND m.courseId = c.id  AND m.isLearned = ? AND m.role = ?";

        return $this->getConnection()->fetchColumn($sql,array($userId, $type, $isLearned, $role));
    }

    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $sql  = "SELECT m.* FROM {$this->table} m ";
        $sql.= ' JOIN  '. CourseDao::TABLENAME . ' AS c ON m.userId = ? ';
        $sql .= " AND c.type =  ? AND m.courseId = c.id AND m.isLearned = ? AND m.role = ?";
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $type, $isLearned, $role));
    }

    public function findAllMemberByUserIdAndRole($userId, $role, $onlyPublished = true)
    {
        $this->filterStartLimit($start, $limit);

        $sql  = "SELECT m.* FROM {$this->table} m ";
        $sql.= ' JOIN  '. CourseDao::TABLENAME . ' AS c ON m.userId = ? ';
        $sql .= " AND m.role =  ? AND m.courseId = c.id ";
        if($onlyPublished){
            $sql .= " AND c.status = 'published' ";
        }

        // $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($userId, $role));
    }

    public function findMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  userId = ? AND role = ? AND isLearned = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $role, $isLearned));
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
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND role = ? ORDER BY seq,createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId, $role));
    }

    public function findMemberCountByCourseIdAndRole($courseId, $role)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND role = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $role));
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

    public function searchMember($conditions, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'ASC');
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function countMembersByStartTimeAndEndTime($startTime,$endTime)
    {
        $sql = "SELECT * FROM (SELECT courseId, count(userId) AS co,role FROM {$this->table} WHERE createdTime <  ? AND createdTime > ? AND role='student'  GROUP BY courseId) coursemembers ORDER BY coursemembers.co DESC LIMIT 0,5";
        return $this->getConnection()->fetchAll($sql, array($endTime,$startTime));
    }

    public function searchMemberIds($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions);

        if(isset($conditions['unique'])){
            $builder->select('DISTINCT userId');
        }else {
            $builder->select('userId');
        }
        $builder->orderBy($orderBy[0], $orderBy[1]);
        $builder->setFirstResult($start);
        $builder->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function updateMember($id, $member)
    {
        $this->getConnection()->update($this->table, $member, array('id' => $id));
        return $this->getMember($id);
    }

    public function deleteMember($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteMembersByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE userId = ? AND courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($userId, $courseId));
    }

    public function findCourseMembersByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = 'student' AND deadlineNotified=0 AND deadline>0 LIMIT 0,10";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND role = 'student' AND courseId in ($marks)";
        return $this->getConnection()->fetchAll($sql, array_merge(array($studentId), $courseIds));
    }

    private function _createSearchQueryBuilder($conditions)
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
            ->andWhere('courseId IN (:courseIds)');

        return $builder;
    }

}