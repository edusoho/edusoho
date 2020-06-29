<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
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
            $categoryTree = ArrayToolkit::index($categoryTree, 'id');
            foreach ($categoryTree as &$tree) {
                $tree['isShowNum'] = 0 == $tree['parent_id'] ? 1 : (0 == $categoryTree[$tree['parent_id']]['question_num'] ? 0 : 1);
            }
        }

        return $this->render('item-bank-exercise/chapter-exercise/list.html.twig', [
            'exercise' => $exercise,
            'categoryTree' => $categoryTree,
            'questionBank' => $questionBank,
        ]);
    }

    public function openAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $chapterEnable = 'on' == $request->get('chapterEnable') ? 1 : 0;
        $this->getExerciseService()->updateChapterEnable($exercise['id'], ['chapterEnable' => $chapterEnable]);

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
}
