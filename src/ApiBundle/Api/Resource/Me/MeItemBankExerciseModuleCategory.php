<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class MeItemBankExerciseModuleCategory extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseModuleCategoryFilter", mode="public")
     */
    public function search(ApiRequest $request, $exerciseId, $moduleId)
    {
        $user = $this->getCurrentUser();

        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        if (empty($itemBankExercise)) {
            return [];
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);
        $categories = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBank']['id']);

        $answerRecords = $this->getItemBankChapterExerciseRecordService()->search(
            ['userId' => $user['id'], 'moduleId' => $moduleId],
            [],
            0,
            PHP_INT_MAX
        );
        $answerRecordGroups = ArrayToolkit::group($answerRecords, 'itemCategoryId');
        foreach ($categories as &$category) {
            if (!empty($answerRecordGroups[$category['id']])) {
                $category['latestAnswerRecord'] = end($answerRecordGroups[$category['id']]);
            }
        }

        return $categories;
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->service('ItemBankExercise:ChapterExerciseRecordService');
    }
}
