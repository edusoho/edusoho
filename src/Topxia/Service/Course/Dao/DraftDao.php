<?php

namespace Topxia\Service\Course\Dao;

interface LessonDao
{
    public function findDraftsByCourseId($courseId,$userId);

    public function getDraft($id);

    public function getDrafts($courseId,$userId);

    public function deleteDraft($courseId,$userId);

    public function addDraft($lesson);

    public function updateTextDraft($userId,$courseId, $fields);
}