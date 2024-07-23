<?php

namespace Biz\Activity\Service\Impl;

use Biz\Activity\Dao\ExerciseActivityDao;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\BaseService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

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

    public function isExerciseAssessment($assessmentId, $exerciseActivity)
    {
        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);
        $range = $exerciseActivity['drawCondition']['range'];
        $section = $exerciseActivity['drawCondition']['section'];
        if (empty($assessment)) {
            return false;
        }

        if ($assessment['displayable']) {
            return false;
        }

        if ($assessment['bank_id'] != $range['bank_id']) {
            return false;
        }

        if ($assessment['item_count'] != $section['item_count']) {
            return false;
        }

        return true;
    }

    public function createExerciseAssessment($activity)
    {
        $range = $activity['ext']['drawCondition']['range'];
        $sections = $this->getAssessmentService()->drawItems(
            $range,
            [$activity['ext']['drawCondition']['section']]
        );

        $assessment = [
            'name' => $activity['title'],
            'displayable' => 0,
            'description' => '',
            'bank_id' => $range['bank_id'],
            'sections' => $sections,
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->getAssessmentService()->openAssessment($assessment['id']);

        return $assessment;
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

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }
}
