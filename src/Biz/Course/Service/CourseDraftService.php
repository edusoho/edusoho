<?php

namespace Biz\Course\Service;

use Biz\System\Annotation\Log;

interface CourseDraftService
{
    public function getCourseDraft($id);

    public function getCourseDraftByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId);

    public function createCourseDraft($draft);

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     * @Log(module="course",action="update_draft",param="courseId")
     */
    public function updateCourseDraft($id, $fields);

    public function deleteCourseDrafts($courseId, $activityId, $userId);
}
