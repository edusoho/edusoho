<?php

namespace Biz\Activity\Service;

interface HomeworkActivityService
{
    public function create($homeworkActivity);

    public function update($homeworkActivityId, array $fields);

    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id');

    public function getByAnswerSceneId($answerSceneId);

    public function getByAssessmentId($assessmentId);

    public function get($id);

    public function findByIds($ids);

    public function delete($id);

    public function findByAnswerSceneIds($answerSceneIds);

    public function findByAssessmentId($assessmentId);
}
