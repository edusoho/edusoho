<?php

namespace Topxia\Service\Course\Dao;

interface CourseDraftDao
{
    public function getCourseDraft($id);

    public function getCourseDrafts($courseId,$lessonId, $userId);

    public function deleteCourseDrafts($courseId,$lessonId, $userId);

    public function addDraft($draft);

    public function updateCourseDraft($courseId,$lessonId, $userId,$fields);
}