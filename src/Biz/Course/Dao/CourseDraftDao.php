<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseDraftDao extends GeneralDaoInterface
{
    public function findCourseDraft($courseId, $lessonId, $userId);

    public function deleteCourseDrafts($courseId, $lessonId, $userId);
}
