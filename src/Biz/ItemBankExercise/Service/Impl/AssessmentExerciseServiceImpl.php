<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\Accessor\AccessorInterface;
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

    public function findByModuleIds($moduleIds)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByModuleIds($moduleIds);
    }

    public function findByExerciseIdAndModuleId($exerciseId, $moduleId)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByExerciseIdAndModuleId($exerciseId, $moduleId);
    }

    public function getByModuleIdAndAssessmentId($moduleId, $assessmentId)
    {
        return $this->getItemBankAssessmentExerciseDao()->getByModuleIdAndAssessmentId($moduleId, $assessmentId);
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

            $assessmentExercise = $this->getByModuleIdAndAssessmentId($moduleId, $assessmentId);
            $module = $this->getItemBankExerciseModuleService()->get($moduleId);
            $answerRecord = $this->getAnswerService()->startAnswer($module['answerSceneId'], $assessmentId, $userId);

            $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseRecordService()->create([
                'moduleId' => $moduleId,
                'exerciseId' => $module['exerciseId'],
                'assessmentId' => $assessmentId,
                'assessmentExerciseId' => $assessmentExercise['id'],
                'userId' => $userId,
                'answerRecordId' => $answerRecord['id'],
            ]);

            $this->getUserFootprintService()->createUserFootprint([
                'targetType' => 'item_bank_assessment_exercise',
                'targetId' => $assessmentExercise['id'],
                'event' => 'answer.started',
                'userId' => $assessmentExerciseRecord['userId'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $assessmentExerciseRecord;
    }

    public function addAssessments($exerciseId, $moduleId, $assessments)
    {
        try {
            $this->beginTransaction();

            foreach ($assessments as $assessment) {
                if ($this->getItemBankAssessmentExerciseDao()->isAssessmentExercise($moduleId, $assessment['id'], $exerciseId)) {
                    $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXERCISE_EXIST());
                }

                $this->getItemBankAssessmentExerciseDao()->create(
                    [
                        'exerciseId' => $exerciseId,
                        'moduleId' => $moduleId,
                        'assessmentId' => $assessment['id'],
                    ]
                );
            }
            $this->dispatchEvent('assessmentExercise.create', $assessments, ['exerciseId' => $exerciseId, 'moduleId' => $moduleId]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function isAssessmentExercise($moduleId, $assessmentId, $exerciseId)
    {
        $assessmentExercise = $this->getItemBankAssessmentExerciseDao()->isAssessmentExercise($moduleId, $assessmentId, $exerciseId);

        return empty($assessmentExercise) ? false : true;
    }

    protected function canStartAnswer($moduleId, $assessmentId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_ASSESSMENT != $module['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->count(['moduleId' => $moduleId, 'assessmentId' => $assessmentId])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $access = $this->getItemBankExerciseService()->canLearnExercise($module['exerciseId']);
        if (AccessorInterface::SUCCESS != $access['code']) {
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

    public function deleteAssessmentExercise($id)
    {
        $assessmentExercise = $this->getItemBankAssessmentExerciseDao()->get($id);
        if (empty($assessmentExercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $result = $this->getItemBankAssessmentExerciseDao()->delete($id);
        $this->dispatchEvent('assessmentExercise.delete', $assessmentExercise);

        return $result;
    }

    public function batchDeleteAssessmentExercise($ids)
    {
        if (empty($ids)) {
            return;
        }

        $this->getItemBankAssessmentExerciseDao()->batchDelete(['ids' => $ids]);
    }

    public function getAssessmentCountGroupByExerciseId($ids)
    {
        return $this->getItemBankAssessmentExerciseDao()->getAssessmentCountGroupByExerciseId($ids);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        $assessment = $this->getItemBankAssessmentExerciseDao()->getByAssessmentId($assessmentId);
        $result = $this->getItemBankAssessmentExerciseDao()->deleteByAssessmentId($assessmentId);

        $this->dispatch('assessmentExercise.delete', $assessment);

        return $result;
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

    /**
     * @return \Biz\User\UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->createService('User:UserFootprintService');
    }
}
