<?php

namespace Biz\Course\Dao;

interface CourseMemberDao
{
    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findStudentsByCourseId($courseId);

}
