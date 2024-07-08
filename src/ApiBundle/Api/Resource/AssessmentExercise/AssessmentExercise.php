<?php

namespace ApiBundle\Api\Resource\AssessmentExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class AssessmentExercise extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['exerciseId', 'moduleId', 'ids'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $exercise = $this->getExerciseService()->tryManageExercise($fields['exerciseId']);

        if (!$this->getQuestionBankService()->canManageBank($exercise['questionBankId'])) {
            throw CommonException::FORBIDDEN_FREQUENT_OPERATION();
        }
        $assessments = $this->getAssessmentService()->findAssessmentsByIds($fields['ids']);
        if (empty($assessments)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $this->getAssessmentExerciseService()->addAssessments($fields['exerciseId'], $fields['moduleId'], $assessments);

        return ['ok' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $exercise = $this->getExerciseService()->tryManageExercise($conditions['exerciseId']);
        $modules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], ExerciseModuleService::TYPE_ASSESSMENT);
        $moduleIds = ArrayToolkit::column($modules, 'id');
        if (empty($modules)) {
            throw ItemBankExerciseException::NOTFOUND_MODULE();
        }
        $moduleId = $conditions['moduleId'];
        if (!empty($moduleId) && !in_array($moduleId, $moduleIds)) {
            $moduleId = $modules[0]['id'];
        }
        $moduleId = empty($moduleId) ? $modules[0]['id'] : $moduleId;
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getAssessmentExerciseService()->count($conditions);
        $assessmentExercises = $this->getAssessmentExerciseService()->search(
            ['moduleId' => $moduleId],
            ['createdTime' => 'ASC'],
            $offset,
            $limit
        );
        $assessmentIds = ArrayToolkit::column($assessmentExercises, 'assessmentId');
        $assessments = $this->getAssessmentService()->findAssessmentsByIds($assessmentIds);
        $AssessmentGenerateRules = $this->getAssessmentGenerateRuleService()->findAssessmentGenerateRuleByAssessmentIds($assessmentIds);
        $numMap = array_column($AssessmentGenerateRules, 'num', 'assessment_id');
        $assessmentMap = array_column($assessments, null, 'id');
        $assessmentExercises = array_map(function ($exercise) use ($assessmentMap, $numMap) {
            $assessmentId = $exercise['assessmentId'];
            if (isset($assessmentMap[$assessmentId])) {
                $exercise['assessment'] = $assessmentMap[$assessmentId];
                $exercise['assessment']['num'] = $numMap[$assessmentId] ?? null;
            }

            return $exercise;
        }, $assessmentExercises);

        return $this->makePagingObject($assessmentExercises, $total, $limit, $offset);
    }

    public function remove(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['exerciseId', 'id'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getExerciseService()->tryManageExercise($fields['exerciseId']);
        $this->getAssessmentExerciseService()->deleteAssessmentExercise($fields['id']);

        return ['ok' => true];
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    private function getAssessmentGenerateRuleService()
    {
        return $this->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }
}
