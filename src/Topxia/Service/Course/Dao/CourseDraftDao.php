<?php

namespace Topxia\Service\Course\Dao;

interface CourseDraftDao
{
    public function getCourseDraft($id);

    public function findCourseDraft($courseId,$lessonId, $userId);

    public function deleteCourseDrafts($courseId,$lessonId, $userId);

    public function addCourseDraft($draft);

    public function updateCourseDraft($courseId,$lessonId, $userId,$fields);
}