<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;

class AssessmentExerciseServiceImpl extends BaseService implements AssessmentExerciseService
{
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
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (!$this->getItemBankExerciseService()->canLearningExercise($module['exerciseId'], $userId)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        if (!$this->canStartAnswer($module, $assessmentId, $userId)) {
            $this->createNewException(ItemBankExerciseException::CANNOT_START_CHAPTER_ANSWER());
        }

        try {
            $this->beginTransaction();

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

    //todo
    protected function canStartAnswer($module, $assessmentId, $userId)
    {
        //模块是否存在
        //试卷练习是否开启
        //试卷是否在应模块下
        //是否已有正在进行中的答题记录
        return true;
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

    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseDao');
    }
}
