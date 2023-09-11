<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $categories = $this->getItemCategoryService()->findItemCategoriesByIds($ids);
        $parentIds = array_unique(array_column($categories, 'parent_id'));

        $hiddenChapterIds = $exercise['hiddenChapterIds'] ? explode(',', $exercise['hiddenChapterIds']) : [];

        foreach ($parentIds as $parentId) {
            if (empty($hiddenChapterIds) && $parentId && !in_array($parentId, $ids)) {
                return $this->createJsonResponse([
                    'success' => false,
                    'message' => '请先发布上一级章节',
                ]);
            }

            if ($hiddenChapterIds && $parentId && !in_array($parentId, $ids)) {
                return $this->createJsonResponse([
                    'success' => false,
                    'message' => '请先发布上一级章节',
                ]);
            }
        }

        if (empty($hiddenChapterIds)) {
            $updateHiddenChapterIds = array_diff($ids, $hiddenChapterIds);
        } else {
            $updateHiddenChapterIds = array_diff($ids, $hiddenChapterIds);
            $updateHiddenChapterIds = array_merge($hiddenChapterIds, $updateHiddenChapterIds);
        }

        $this->getExerciseService()->update($exerciseId, ['hiddenChapterIds' => implode(',', $updateHiddenChapterIds)]);

        $this->dispatchEvent('itemBankExerciseChapter.publish', new Event($exercise));
        $this->getLogService()->info('item_bank_exercise', 'publish_exercise_chapter', "管理员{$this->getCurrentUser()['nickname']}发布题库练习《{$exercise['title']}》的章节");

        return $this->createJsonResponse(['success' => true]);
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
     * @var EventDispatcherInterface
     */
    protected function dispatchEvent($eventName, Event $event)
    {
        return $this->getBiz()['dispatcher']->dispatch($eventName, $event);
    }
}
