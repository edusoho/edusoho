<?php

namespace Topxia\Service\Course\Dao;

interface DraftDao
{
    public function getDraft($id);

    public function getCourseDrafts($courseId,$userId,$lessonId);

    public function deleteDraftByCourse($courseId,$userId,$lessonId);

    public function addDraft($draft);

    public function updateCourseDraft($userId,$courseId,$lessonId,$fields);
}