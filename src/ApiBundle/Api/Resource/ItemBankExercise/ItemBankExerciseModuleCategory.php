<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ItemBankExerciseModuleCategory extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId, $moduleId)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        if (empty($itemBankExercise)) {
            return [];
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExercise['questionBankId']);

        return $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBank']['id']);
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
}
