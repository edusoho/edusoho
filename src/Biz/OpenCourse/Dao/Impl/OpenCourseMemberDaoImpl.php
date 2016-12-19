<?php
namespace Topxia\Service\OpenCourse\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Topxia\Service\Common\BaseDao;
use Topxia\Service\OpenCourse\Dao\OpenCourseMemberDao;

class OpenCourseMemberDaoImpl extends GeneralDaoImpl implements OpenCourseMemberDao
{
    protected $table = 'open_course_member';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array(),
            'orderbys'   => array('createdTime', 'seq', 'lastEnterTime'),
            'conditions' => array(
                'userId = :userId',
                'userId > :userIdGT',
                'courseId = :courseId',
                'role = :role',
                'isNotified = :isNotified',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
                'courseId IN (:courseIds)',
                'userId IN (:userIds)',
                'mobile = :mobile'
            )
        );
    }

    public function getByUserIdAndCourseId($courseId, $userId)
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId));
    }

    public function getByIpAndCourseId($courseId, $ip)
    {
        return $this->getByFields(array('ip' => $ip, 'courseId' => $courseId));
    }

    public function getByMobileAndCourseId($courseId, $mobile)
    {
        return $this->getByFields(array('mobile' => $mobile, 'courseId' => $courseId));
    }

    public function findByCourseIds($courseIds)
    {
        return $this->findInField('courseId', $courseIds);
    }

    public function deleteByCourseId($courseId)
    {
        $result = $this->getConnection()->delete($this->table, array('courseId' => $courseId));
        $this->clearCached();
        return $result;
    }

    public function findByCourseIdAndRole($courseId, $role, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND role = ? ORDER BY seq, createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($courseId, $role));
    }
}
