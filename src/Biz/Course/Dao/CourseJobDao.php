<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseJobDao extends GeneralDaoInterface
{
    public function getByTypeAndCourseId($type, $courseId);
}
