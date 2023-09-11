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

    public function unpublishAction(Request $request, $exerciseId)
    {
        $ids = $request->request->get('ids');
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        $categoryTrees = $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']);

        $parentIds = [];
        $childrenIds = [];
        foreach ($categoryTrees as $categoryTree) {
            if (!empty($categoryTree['children'])) {
                $this->traverseChildren($categoryTree['children'], $parentIds, $childrenIds);
            }
        }

        $unPublishIds = [];
        foreach ($ids as $id) {
            if (in_array($id, array_unique($parentIds))) {
                foreach ($childrenIds[$id] as $childId) {
                    if (in_array($childId, array_unique($parentIds))) {
                        $unPublishIds = array_merge($ids, $childrenIds[$childId]);
                    }
                }
                $unPublishIds = array_merge($unPublishIds, $childrenIds[$id]);
            }
        }
        $unPublishIds = array_unique(array_merge($unPublishIds, $ids));

        $hiddenChapterIds = $exercise['hiddenChapterIds'] ? explode(',', $exercise['hiddenChapterIds']) : [];
        $updateHiddenChapterIds = array_diff($hiddenChapterIds, $unPublishIds);

        $this->getExerciseService()->update($exerciseId, ['hiddenChapterIds' => implode(',', $updateHiddenChapterIds)]);

        $this->dispatchEvent('itemBankExerciseChapter.unpublish', new Event($exercise));
        $this->getLogService()->info('item_bank_exercise', 'unpublish_exercise_chapter', "管理员{$this->getCurrentUser()['nickname']}取消发布题库练习《{$exercise['title']}》的章节");

        return $this->createJsonResponse(['success' => true]);
    }

    protected function traverseChildren($children, &$parentIds, &$childrenIds)
    {
        foreach ($children as $child) {
            $parentIds[] = $child['parent_id'];
            $childrenIds[$child['parent_id']][] = $child['id'];

            if (!empty($child['children'])) {
                $this->traverseChildren($child['children'], $parentIds, $childrenIds);
            }
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

    /**
     * @var EventDispatcherInterface
     */
    protected function dispatchEvent($eventName, Event $event)
    {
        return $this->getBiz()['dispatcher']->dispatch($eventName, $event);
    }
}
