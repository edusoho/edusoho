<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\TestpaperException;
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
        if (empty($modules)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_MODULE());
        }

        if (!empty($moduleId) && !in_array($moduleId, $moduleIds)) {
            $moduleId = $modules[0]['id'];
        }

        $moduleId = empty($moduleId) ? $modules[0]['id'] : $moduleId;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentExerciseService()->count(['moduleId' => $moduleId]),
            20
        );

        $assessmentExercises = $this->getAssessmentExerciseService()->search(
            ['moduleId' => $moduleId],
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));

        return $this->render('item-bank-exercise/assessment-exercise/index.html.twig', [
            'exercise' => $exercise,
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            'modules' => $modules,
            'moduleId' => $moduleId,
            'assessments' => $assessments,
            'paginator' => $paginator,
            'assessmentExercises' => ArrayToolkit::index($assessmentExercises, 'assessmentId'),
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

    public function assessmentAddListAction(Request $request, $exerciseId, $moduleId, $isPage)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if (!$this->getQuestionBankService()->canManageBank($exercise['questionBankId'])) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);

        $conditions = [
            'bank_id' => $questionBank['itemBankId'],
            'displayable' => 1,
        ];

        $assessmentExercises = $this->getAssessmentExerciseService()->findByExerciseIdAndModuleId($exerciseId, $moduleId);
        $assessmentIds = ArrayToolkit::column($assessmentExercises, 'assessmentId');
        $conditions['ids'] = !empty($assessmentIds) ? $assessmentIds : [];
        $conditions['status'] = 'open';

        $paginator = new Paginator(
            $request,
            $this->getAssessmentService()->countAssessments($conditions),
            10
        );

        $assessments = $this->getAssessmentService()->searchAssessments(
            $conditions,
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $route = $isPage ? 'item-bank-exercise/assessment-exercise/assessment-list-tr.html.twig' : 'item-bank-exercise/assessment-exercise/assessment-modal.html.twig';

        return $this->render($route, [
            'exercise' => $exercise,
            'questionBank' => $questionBank,
            'testpapers' => $assessments,
            'users' => $this->getUserService()->findUsersByIds(array_column($assessments, 'updated_user_id')),
            'paginator' => $paginator,
            'moduleId' => $moduleId,
        ]);
    }

    public function deleteModuleAction(Request $request, $exerciseId, $moduleId)
    {
        $this->getExerciseService()->tryManageExercise($exerciseId);

        $this->getExerciseModuleService()->deleteAssessmentModule($moduleId);

        return $this->createJsonResponse(true);
    }

    public function addAssessmentAction(Request $request, $exerciseId, $moduleId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if (!$this->getQuestionBankService()->canManageBank($exercise['questionBankId'])) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids');

        $assessments = $this->getAssessmentService()->findAssessmentsByIds($ids);
        if (empty($assessments)) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getAssessmentExerciseService()->addAssessments($exerciseId, $moduleId, $assessments);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAssessmentAction(Request $request, $exerciseId)
    {
        $this->getExerciseService()->tryManageExercise($exerciseId);

        $ids = $request->request->get('ids');
        $this->getAssessmentExerciseService()->batchDeleteAssessmentExercise($ids);

        return $this->createJsonResponse(true);
    }

    public function deleteAssessmentAction(Request $request, $exerciseId, $id)
    {
        $this->getExerciseService()->tryManageExercise($exerciseId);

        $this->getAssessmentExerciseService()->deleteAssessmentExercise($id);

        return $this->createJsonResponse(true);
    }

    public function openAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $assessmentEnable = 'true' == $request->get('assessmentEnable') ? 1 : 0;
        $this->getExerciseService()->updateModuleEnable($exercise['id'], ['assessmentEnable' => $assessmentEnable]);

        return $this->createJsonResponse(true);
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
