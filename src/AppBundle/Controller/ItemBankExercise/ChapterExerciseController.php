<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ChapterExerciseController extends BaseController
{
    public function listAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        $chapterTree = [];
        if ($exercise['chapterEnable']) {
            $chapterTree = $this->getItemBankChapterExerciseService()->getChapterTreeList($questionBank['id']);
        }

        return $this->render('item-bank-exercise-manage/chapter-exercise/list.html.twig', [
            'exercise' => $exercise,
            'categoryTree' => $chapterTree,
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
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseService');
    }
}
