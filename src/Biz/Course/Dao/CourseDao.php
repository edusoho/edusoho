<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseDao extends GeneralDaoInterface
{
    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);
}
