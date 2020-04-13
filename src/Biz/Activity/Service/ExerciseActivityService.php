<?php

namespace Biz\Activity\Service;

interface ExerciseActivityService
{
    public function getActivity($id);

    public function findActivitiesByIds($ids);

    public function createActivity($fields);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);

    public function getByAnswerSceneId($answerSceneId);
}
