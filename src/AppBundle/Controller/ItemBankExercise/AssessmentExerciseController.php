<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Symfony\Component\HttpFoundation\Request;

class AssessmentExerciseController extends BaseController
{
    public function indexAction(Request $request, $exerciseId, $moduleId = 0)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $modules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], ExerciseModuleService::TYPE_ASSESSMENT);
        $moduleIds = ArrayToolkit::column($modules, 'id');
        if (empty($modules) || (!empty($moduleId) && !in_array($moduleId, $moduleIds))) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_MODULE());
        }

        $moduleId = empty($moduleId) ? $modules[0]['id'] : $moduleId;

        $assessmentExercises = $this->getAssessmentExerciseService()->findByModuleId($moduleId);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));

        return $this->render('item-bank-exercise/assessment-exercise/index.html.twig', [
            'exercise' => $exercise,
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            'modules' => $modules,
            'moduleId' => $moduleId,
            'assessments' => $assessments,
        ]);
    }

    public function editModuleAction(Request $request, $exerciseId, $moduleId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $module = $this->getExerciseModuleService()->get($moduleId);
        if (empty($module)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_MODULE());
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $module = $this->getExerciseModuleService()->updateAssessmentModule($moduleId, $data);

            return $this->createJsonResponse($module);
        }

        return $this->render('item-bank-exercise/assessment-exercise/module-modal.html.twig', [
            'module' => $module,
            'exercise' => $exercise,
        ]);
    }

    public function createModuleAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('title');

            $module = $this->getExerciseModuleService()->createAssessmentModule($exerciseId, $name);

            return $this->createJsonResponse($module);
        }

        return $this->render('item-bank-exercise/assessment-exercise/module-modal.html.twig', [
            'exercise' => $exercise,
            'module' => [],
        ]);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }
}
