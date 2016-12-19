<?php

namespace Topxia\Service\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OpenCourseDao extends GeneralDaoInterface
{
    public function findCoursesByIds(array $ids);

    public function waveCourse($id, $field, $diff);
}
