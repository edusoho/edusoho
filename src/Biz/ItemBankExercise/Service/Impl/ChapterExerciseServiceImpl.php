<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;

class ChapterExerciseServiceImpl extends BaseService implements ChapterExerciseService
{
    public function startAnswer($moduleId, $categroyId, $userId, $exerciseMode = 0)
    {
        $this->canStartAnswer($moduleId, $categroyId, $userId);

        try {
            $this->beginTransaction();

            $module = $this->getItemBankExerciseModuleService()->get($moduleId);

            $assessment = $this->createAssessmentByCategroyId($categroyId, $module);

            $answerRecord = $this->getAnswerService()->startAnswer($module['answerSceneId'], $assessment['id'], $userId);
            $answerRecord = $this->getAnswerRecordService()->update($answerRecord['id'], ['exercise_mode' => $exerciseMode]);

            $chapterExerciseRecord = $this->getItemBankChapterExerciseRecordService()->create([
                'moduleId' => $moduleId,
                'exerciseId' => $module['exerciseId'],
                'itemCategoryId' => $categroyId,
                'userId' => $userId,
                'answerRecordId' => $answerRecord['id'],
                'questionNum' => $assessment['question_count'],
            ]);

            $this->getUserFootprintService()->createUserFootprint([
                'targetType' => 'item_bank_chapter_exercise',
                'targetId' => $categroyId,
                'event' => 'answer.started',
                'userId' => $chapterExerciseRecord['userId'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $chapterExerciseRecord;
    }

    public function getChapter($chapterId)
    {
        return $this->getItemCategoryService()->getItemCategory($chapterId);
    }

    public function findChaptersByIds($ids)
    {
        return $this->getItemCategoryService()->findItemCategoriesByIds($ids);
    }

    public function getPublishChapterTree($questionBankId)
    {
        $exercise = $this->getItemBankExerciseService()->getByQuestionBankId($questionBankId);
        if (empty($exercise)) {
            return [];
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        if (empty($questionBank)) {
            return [];
        }
        $chapters = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
        if (empty($chapters)) {
            return [];
        }

        $chapters = $this->filterHiddenChapters($chapters, $exercise['hiddenChapterIds']);

        return $this->getItemCategoryService()->buildCategoryTree($chapters);
    }

    public function getPublishChapterTreeList($questionBankId)
    {
        $exercise = $this->getItemBankExerciseService()->getByQuestionBankId($questionBankId);
        if (empty($exercise)) {
            return [];
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        if (empty($questionBank)) {
            return [];
        }
        $chapters = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
        if (empty($chapters)) {
            return [];
        }

        $chapters = $this->filterHiddenChapters($chapters, $exercise['hiddenChapterIds']);

        return $this->getItemCategoryService()->buildCategoryTreeList($chapters, 0);
    }

    public function getChapterTreeList($questionBankId)
    {
        $exercise = $this->getItemBankExerciseService()->getByQuestionBankId($questionBankId);
        if (empty($exercise)) {
            return [];
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);
        if (empty($questionBank)) {
            return [];
        }
        $chapters = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBankId']);
        $hiddenChapterIds = array_flip($exercise['hiddenChapterIds']);
        foreach ($chapters as &$chapter) {
            $chapter['status'] = isset($hiddenChapterIds[$chapter['id']]) ? 'unpublished' : 'published';
        }

        return $chapters;
    }

    public function findChapterChildrenIds($questionBankId, $ids)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($questionBankId);

        return $this->getItemCategoryService()->findMultiCategoryChildrenIds($questionBank['itemBankId'], $ids);
    }

    protected function filterHiddenChapters($chapters, $hiddenChapterIds)
    {
        $hiddenChapterIds = array_flip($hiddenChapterIds);

        return array_filter($chapters, function ($chapter) use ($hiddenChapterIds) {
            return !isset($hiddenChapterIds[$chapter['id']]);
        });
    }

    protected function canStartAnswer($moduleId, $categoryId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_CHAPTER != $module['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $access = $this->getItemBankExerciseService()->canLearnExercise($module['exerciseId']);
        if (AccessorInterface::SUCCESS != $access['code']) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $category = $this->getChapter($categoryId);
        if (empty($category)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        if (0 == $category['question_num']) {
            $this->createNewException(AssessmentException::ASSESSMENT_EMPTY());
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($module['exerciseId']);
        if (0 == $itemBankExercise['chapterEnable']) {
            $this->createNewException(ItemBankExerciseException::CHAPTER_EXERCISE_CLOSED());
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);
        if ($questionBank['itemBank']['id'] != $category['bank_id']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $latestRecord = $this->getItemBankChapterExerciseRecordService()->getLatestRecord($moduleId, $categoryId, $userId);
        if (!empty($latestRecord) && AnswerService::ANSWER_RECORD_STATUS_FINISHED != $latestRecord['status']) {
            $this->createNewException(ItemBankExerciseException::CHAPTER_ANSWER_IS_DOING());
        }

        return false;
    }

    protected function createAssessmentByCategroyId($categroyId, $module)
    {
        try {
            $this->beginTransaction();

            $categroy = $this->getChapter($categroyId);
            $itemBank = $this->getItemBankService()->getItemBank($categroy['bank_id']);

            $itemIds = ArrayToolkit::column(
                $this->getItemService()->searchItems(['category_id' => $categroyId], [], 0, PHP_INT_MAX, ['id']),
                'id'
            );
            $items = $this->getItemService()->findItemsByIds($itemIds, true);
            shuffle($items);

            $assessment = [
                'bank_id' => $categroy['bank_id'],
                'name' => $itemBank['name'],
                'displayable' => 0,
                'description' => $this->getAssessmentDescription($categroyId, $module),
                'sections' => [
                    [
                        'name' => '题目列表',
                        'description' => '',
                        'items' => $items,
                    ],
                ],
            ];

            $assessment = $this->getAssessmentService()->createAssessment($assessment);
            $assessment = $this->getAssessmentService()->openAssessment($assessment['id']);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $assessment;
    }

    protected function getAssessmentDescription($categroyId, $module)
    {
        $categories = [];

        $loop = 1;
        while ($loop <= 3) {
            $categroy = $this->getChapter($categroyId);
            if (empty($categroy)) {
                break;
            }
            $categroyId = $categroy['parent_id'];
            array_unshift($categories, $categroy);
            ++$loop;
        }

        return $module['title'].' > '.implode(ArrayToolkit::column($categories, 'name'), ' > ');
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
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->createService('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return \Biz\User\UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->createService('User:UserFootprintService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
