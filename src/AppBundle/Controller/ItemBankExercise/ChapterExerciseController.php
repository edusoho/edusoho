<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Symfony\Component\HttpFoundation\Request;

class ChapterExerciseController extends BaseController
{
    public function listAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        $categoryTree = [];
        if ($exercise['chapterEnable']) {
            $categoryTree = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBankId']);
        }

        return $this->render('item-bank-exercise-manage/chapter-exercise/list.html.twig', [
            'exercise' => $exercise,
            'categoryTree' => $categoryTree,
            'questionBank' => $questionBank,
        ]);
    }

    public function openAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $chapterEnable = 'true' == $request->get('chapterEnable') ? 1 : 0;
        $this->getExerciseService()->updateModuleEnable($exercise['id'], ['chapterEnable' => $chapterEnable]);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $exerciseId)
    {
        $ids = $request->request->get('ids');
        try {
            $this->getExerciseService()->publishExerciseChapter($exerciseId, $ids);

            return $this->createJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['success' => false, 'message' => $this->trans($e->getMessage())]);
        }
    }

    public function unpublishAction(Request $request, $exerciseId)
    {
        $ids = $request->request->get('ids');
        try {
            $this->getExerciseService()->unpublishExerciseChapter($exerciseId, $ids);

            return $this->createJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['success' => false, 'message' => $this->trans($e->getMessage())]);
        }
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
}
