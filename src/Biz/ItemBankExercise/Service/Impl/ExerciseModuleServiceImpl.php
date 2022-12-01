<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class ExerciseModuleServiceImpl extends BaseService implements ExerciseModuleService
{
    public function findByExerciseId($exerciseId)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseId($exerciseId);
    }

    public function findByExerciseIdAndType($exerciseId, $type)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseIdAndType($exerciseId, $type);
    }

    public function get($id)
    {
        return $this->getItemBankExerciseModuleDao()->get($id);
    }

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getItemBankExerciseModuleDao()->getByAnswerSceneId($answerSceneId);
    }

    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankExerciseModuleDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankExerciseModuleDao()->count($conditions);
    }

    public function createAssessmentModule($exerciseId, $name)
    {
        $this->getItemBankExerciseService()->tryManageExercise($exerciseId);
        $moduleCount = $this->getItemBankExerciseModuleDao()->count(['exerciseId' => $exerciseId, 'type' => 'assessment']);
        if ($moduleCount >= self::ASSESSMENT_MODULE_COUNT) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXCEED());
        }
        try {
            $this->beginTransaction();

            $scene = $this->createAssessmentScene($name);
            $module = $this->getItemBankExerciseModuleDao()->create([
                'exerciseId' => $exerciseId,
                'title' => $name,
                'type' => 'assessment',
                'answerSceneId' => $scene['id'],
            ]);
            $this->dispatchEvent('exercise.assessmentModule.create', $module, ['exerciseId' => $exerciseId]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $module;
    }

    protected function createAssessmentScene($name)
    {
        return $this->getAnswerSceneService()->create(
            [
                'name' => $name,
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 1,
                'enable_facein' => 0,
                'pass_score' => 0,
                'manual_marking' => 1,
                'start_time' => 0,
                'doing_look_analysis' => 0,
            ]
        );
    }

    public function updateAssessmentModule($moduleId, $fields)
    {
        if (!ArrayToolkit::requireds($fields, ['title'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = ArrayToolkit::parts($fields, ['title']);

        $module = $this->get($moduleId);
        $updateModule = $this->getItemBankExerciseModuleDao()->update($moduleId, $fields);
        $this->dispatchEvent('exercise.assessmentModule.update', $module, ['fields' => $fields]);

        return $updateModule;
    }

    public function deleteAssessmentModule($moduleId)
    {
        try {
            $this->beginTransaction();

            $module = $this->getItemBankExerciseModuleDao()->get($moduleId);
            $this->getItemBankExerciseModuleDao()->delete($moduleId);

            $assessmentExercises = $this->getAssessmentExerciseService()->findByModuleId($moduleId);
            $this->getAssessmentExerciseService()->batchDeleteAssessmentExercise(ArrayToolkit::column($assessmentExercises, 'id'));

            $this->dispatchEvent('exercise.assessmentModule.delete', $module);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    /**
     * @return ExerciseModuleDao
     */
    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }
}
