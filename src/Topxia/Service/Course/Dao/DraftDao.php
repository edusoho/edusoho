<?php

namespace Topxia\Service\Course\Dao;

interface DraftDao
{
    // public function findDraftsByCourseId($courseId,$userId);

    public function getDraft($id);

    public function getDrafts($courseId,$userId);

    public function deleteDraftByCourseIdAndUserId($courseId,$userId);

    public function addDraft($lesson);

    public function updateDraft($userId,$courseId, $fields);
}