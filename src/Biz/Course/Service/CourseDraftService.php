<?php

namespace Biz\Course\Service;

interface CourseDraftService
{
    public function getCourseDraft($id);

    public function findCourseDraft($courseId, $lessonId, $userId);

    public function createCourseDraft($draft);

    public function updateCourseDraft($courseId, $lessonId, $userId, $fields);

    public function deleteCourseDrafts($courseId, $lessonId, $userId);
}
