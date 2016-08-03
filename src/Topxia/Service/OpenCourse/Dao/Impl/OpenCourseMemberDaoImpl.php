<?php
namespace Topxia\Service\OpenCourse\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\OpenCourse\Dao\OpenCourseMemberDao;

class OpenCourseMemberDaoImpl extends BaseDao implements OpenCourseMemberDao
{
    protected $table = 'open_course_member';

    public function getMember($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getCourseMember($courseId, $userId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:userId:{$userId}", $courseId, $userId, function ($courseId, $userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND courseId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $courseId)) ?: null;
        }

        );
    }

    public function getCourseMemberByIp($courseId, $ip)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:ip:{$ip}", $courseId, $ip, function ($courseId, $ip) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE ip = ? AND courseId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($ip, $courseId)) ?: null;
        }

        );
    }

    public function getCourseMemberByMobile($courseId, $mobile)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:mobile:{$mobile}", $courseId, $mobile, function ($courseId, $mobile) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND mobile = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($courseId, $mobile)) ?: null;
        }

        );
    }

    public function findMembersByCourseIds($courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->getTable()} WHERE courseId IN ({$marks})";

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
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
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime', 'seq', 'lastEnterTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
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

    public function updateMember($id, $member)
    {
        $this->getConnection()->update($this->table, $member, array('id' => $id));
        $this->clearCached();
        return $this->getMember($id);
    }

    public function deleteMember($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteMembersByCourseId($courseId)
    {
        $result = $this->getConnection()->delete($this->table, array('courseId' => $courseId));
        $this->clearCached();
        return $result;
    }

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND role = ? ORDER BY seq, createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($courseId, $role));
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'open_course_member')
            ->andWhere('userId = :userId')
            ->andWhere('userId > :userIdGT')
            ->andWhere('courseId = :courseId')
            ->andWhere('role = :role')
            ->andWhere('isNotified = :isNotified')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan')
            ->andWhere('courseId IN (:courseIds)')
            ->andWhere('userId IN (:userIds)')
            ->andWhere('mobile = :mobile');
        return $builder;
    }
}
