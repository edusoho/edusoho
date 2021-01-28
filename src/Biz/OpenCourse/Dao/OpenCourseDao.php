<?php

namespace Biz\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OpenCourseDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);
}
