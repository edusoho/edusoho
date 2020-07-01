<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class AssessmentExerciseServiceImpl extends BaseService implements AssessmentExerciseService
{
    public function findByModuleId($moduleId)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByModuleId($moduleId);
    }

    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankAssessmentExerciseDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankAssessmentExerciseDao()->count($conditions);
    }

    public function startAnswer($moduleId, $assessmentId, $userId)
    {
        $this->canStartAnswer($moduleId, $assessmentId, $userId);

        try {
            $this->beginTransaction();

            $module = $this->getItemBankExerciseModuleService()->get($moduleId);

            $answerRecord = $this->getAnswerService()->startAnswer($module['answerSceneId'], $assessmentId, $userId);

            $this->getItemBankAssessmentExerciseRecordService()->create([
                'moduleId' => $moduleId,
                'exerciseId' => $module['exerciseId'],
                'assessmentId' => $assessmentId,
                'userId' => $userId,
                'answerRecordId' => $answerRecord['id'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $answerRecord;
    }

    protected function canStartAnswer($moduleId, $assessmentId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_ASSESSMENT != $module['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->getItemBankAssessmentExerciseDao()->count(['moduleId' => $moduleId, 'assessmentId' => $assessmentId])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->getItemBankExerciseService()->canLearningExercise($module['exerciseId'], $userId)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($module['exerciseId']);
        if (0 == $itemBankExercise['assessmentEnable']) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXERCISE_CLOSED());
        }

        $latestRecord = $this->getItemBankAssessmentExerciseRecordService()->getLatestRecord($moduleId, $assessmentId, $userId);
        if (!empty($latestRecord) && AnswerService::ANSWER_RECORD_STATUS_FINISHED != $latestRecord['status']) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_ANSWER_IS_DOING());
        }

        return false;
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return AssessmentExerciseDao
     */
    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseDao');
    }
}
