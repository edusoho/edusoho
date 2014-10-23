<?php

namespace Topxia\Service\Course\Dao;

interface DraftDao
{
    public function getDraft($id);

    public function getEditDrafts($courseId,$userId,$lessonId);

    public function deleteDraftByCourse($courseId,$userId,$lessonId);

    public function addDraft($draft);

    public function updateEditDraft($userId,$courseId,$lessonId,$fields);
}