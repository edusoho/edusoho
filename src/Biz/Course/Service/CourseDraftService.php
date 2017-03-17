<?php

namespace Biz\Course\Service;

interface CourseDraftService
{
    public function getCourseDraft($id);

    public function getCourseDraftByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId);

    public function createCourseDraft($draft);

    public function updateCourseDraft($id, $fields);

    public function deleteCourseDrafts($courseId, $activityId, $userId);
}
