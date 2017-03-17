<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseDraftDao extends GeneralDaoInterface
{
    public function getByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId);

    public function deleteCourseDrafts($courseId, $activityId, $userId);
}
