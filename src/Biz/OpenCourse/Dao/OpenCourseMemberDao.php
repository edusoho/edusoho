<?php

namespace Biz\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OpenCourseMemberDao extends GeneralDaoInterface
{
    public function getByUserIdAndCourseId($userId, $courseId);

    public function getByIpAndCourseId($ip, $courseId);

    public function getByMobileAndCourseId($courseId, $mobile);

    public function findByCourseIds($courseIds);

    public function deleteByCourseId($courseId);

    public function findByCourseIdAndRole($courseId, $role, $start, $limit);
}
