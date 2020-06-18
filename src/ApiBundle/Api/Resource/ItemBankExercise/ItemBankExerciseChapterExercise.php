<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankExerciseChapterExercise extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $exerciseId, $moduleId)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);

        if (empty($itemBankExercise) || empty($module)) {
            return [
                'module' => (object) [],
                'categories' => [],
            ];
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);

        return [
            'module' => $module,
            'categories' => $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBank']['id']),
        ];
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
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }
}
