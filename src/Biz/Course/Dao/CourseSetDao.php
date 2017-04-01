<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseSetDao extends GeneralDaoInterface
{
    const TABLENAME = 'course_set_v8';

    public function findByIds(array $ids);

    public function findLikeTitle($title);

    public function findCourseSetsByParentIdAndLocked($parentId, $locked);

    public function analysisCourseSetDataByTime($startTime, $endTime);
}
