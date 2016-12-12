<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMemberDao extends GeneralDaoInterface
{
    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findStudentsByCourseId($courseId);

}
