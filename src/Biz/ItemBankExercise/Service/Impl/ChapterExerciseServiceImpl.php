<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ChapterExerciseService;

class ChapterExerciseServiceImpl extends BaseService implements ChapterExerciseService
{
    public function startAnswer($moduleId, $categroyId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (!$this->getItemBankExerciseService()->canLearningExercise($module['exerciseId'], $userId)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        if (!$this->canStartAnswer($moduleId, $categroyId, $userId)) {
            $this->createNewException(ItemBankExerciseException::CANNOT_START_CHAPTER_ANSWER());
        }

        try {
            $this->beginTransaction();

            $assessment = $this->createAssessmentByCategroyId($categroyId);

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

    //todo
    protected function canStartAnswer($moduleId, $categroyId, $userId)
    {
        // 模块是否关闭
        // 分类下数量是否为0
        // 模块跟题目分类是否对应
        return true;
    }

    protected function createAssessmentByCategroyId($categroyId)
    {
        try {
            $this->beginTransaction();

            $categroy = $this->getItemCategoryService()->getItemCategory($categroyId);
            $itemBank = $this->getItemBankService()->getItemBank($categroy['bank_id']);

            $itemIds = ArrayToolkit::column(
                $this->getItemService()->searchItems(['category_id' => $categroyId], [], 0, PHP_INT_MAX, ['id']),
                'id'
            );
            shuffle($itemIds);

            $items = $this->getItemService()->findItemsByIds($itemIds, true);
            $sectionItems = [];
            foreach ($items as $item) {
                $sectionItem = [
                    'id' => $item['id'],
                    'questions' => [],
                ];
                foreach ($item['questions'] as $question) {
                    $sectionItem['questions'][] = [
                        'id' => $question['id'],
                        'score' => 0,
                    ];
                }
                $sectionItems[] = $sectionItem;
            }

            $assessment = [
                'bank_id' => $categroy['bank_id'],
                'name' => $itemBank['name'],
                'displayable' => 0,
                'description' => '章->节', //todo
                'sections' => [
                    [
                        'name' => '',
                        'description' => '章->节',
                        'items' => $sectionItems,
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
}
