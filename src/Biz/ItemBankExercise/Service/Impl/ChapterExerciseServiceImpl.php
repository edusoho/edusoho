<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class ChapterExerciseServiceImpl extends BaseService implements ChapterExerciseService
{
    public function startAnswer($moduleId, $categroyId, $userId)
    {
        $this->canStartAnswer($moduleId, $categroyId, $userId);

        try {
            $this->beginTransaction();

            $module = $this->getItemBankExerciseModuleService()->get($moduleId);

            $assessment = $this->createAssessmentByCategroyId($categroyId, $module);

            $answerRecord = $this->getAnswerService()->startAnswer($module['answerSceneId'], $assessment['id'], $userId);

            $this->getItemBankChapterExerciseRecordService()->create([
                'moduleId' => $moduleId,
                'exerciseId' => $module['exerciseId'],
                'itemCategoryId' => $categroyId,
                'userId' => $userId,
                'answerRecordId' => $answerRecord['id'],
                'questionNum' => $assessment['question_count'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $answerRecord;
    }

    protected function canStartAnswer($moduleId, $categroyId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_CHAPTER != $module['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $access = $this->getItemBankExerciseService()->canLearnExercise($module['exerciseId']);
        if (AccessorInterface::SUCCESS != $access['code']) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $categroy = $this->getItemCategoryService()->getItemCategory($categroyId);
        if (empty($categroy) || 0 == $categroy['question_num']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($module['exerciseId']);
        if (0 == $itemBankExercise['chapterEnable']) {
            $this->createNewException(ItemBankExerciseException::CHAPTER_EXERCISE_CLOSED());
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);
        if ($questionBank['itemBank']['id'] != $categroy['bank_id']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $latestRecord = $this->getItemBankChapterExerciseRecordService()->getLatestRecord($moduleId, $categroyId, $userId);
        if (!empty($latestRecord) && AnswerService::ANSWER_RECORD_STATUS_FINISHED != $latestRecord['status']) {
            $this->createNewException(ItemBankExerciseException::CHAPTER_ANSWER_IS_DOING());
        }

        return false;
    }

    protected function createAssessmentByCategroyId($categroyId, $module)
    {
        try {
            $this->beginTransaction();

            $categroy = $this->getItemCategoryService()->getItemCategory($categroyId);
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
            $categroy = $this->getItemCategoryService()->getItemCategory($categroyId);
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
}
