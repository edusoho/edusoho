<?php

namespace Biz\Activity\Service\Impl;

use Biz\Activity\Dao\ExerciseActivityDao;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\BaseService;

class ExerciseActivityServiceImpl extends BaseService implements ExerciseActivityService
{
    public function getActivity($id)
    {
        return $this->getExerciseActivityDao()->get($id);
    }

    public function findActivitiesByIds($ids)
    {
        return $this->getExerciseActivityDao()->findByIds($ids);
    }

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getExerciseActivityDao()->getByAnswerSceneId($answerSceneId);
    }

    public function createActivity($fields)
    {
        return $this->getExerciseActivityDao()->create($fields);
    }

    public function updateActivity($id, $fields)
    {
        return $this->getExerciseActivityDao()->update($id, $fields);
    }

    public function deleteActivity($id)
    {
        return $this->getExerciseActivityDao()->delete($id);
    }

    /**
     * @return ExerciseActivityDao
     */
    protected function getExerciseActivityDao()
    {
        return $this->createDao('Activity:ExerciseActivityDao');
    }
}
