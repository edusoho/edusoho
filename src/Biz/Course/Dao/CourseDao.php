<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseDao extends GeneralDaoInterface
{
    public function findCoursesPublishedByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function findCoursesByIds($ids);

}
