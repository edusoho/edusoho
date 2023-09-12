<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ChapterExerciseService;

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

        $chapters = $this->getItemBankChapterExerciseService()->getChapterTreeList($exerciseId);

        $answerRecords = $this->getItemBankChapterExerciseRecordService()->search(
            ['userId' => $user['id'], 'moduleId' => $moduleId],
            [],
            0,
            PHP_INT_MAX
        );
        $answerRecordGroups = ArrayToolkit::group($answerRecords, 'itemCategoryId');
        foreach ($chapters as &$chapter) {
            if (!empty($answerRecordGroups[$category['id']])) {
                $chapter['latestAnswerRecord'] = end($answerRecordGroups[$chapter['id']]);
            }
        }

        return $chapters;
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
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

    /**
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->service('ItemBankExercise:ChapterExerciseService');
    }
}
