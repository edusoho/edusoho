<?php

namespace Topxia\Service\Course\Dao;

interface DraftDao
{
    public function getDraft($id);

    public function getDrafts($courseId,$userId);

    public function getEditDrafts($courseId,$userId,$lessonId);

    public function deleteDraftByCourseIdAndUserId($courseId,$userId);

    public function deleteDraftByCourseIdAndUserIdAndLessonId($courseId,$userId,$lessonId);

    public function addDraft($draft);

    public function addEditDraft($draft);

    public function updateDraft($userId,$courseId, $fields);

    public function updateEditDraft($userId,$courseId,$lessonId,$fields);
}