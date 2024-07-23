<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class ItemBankExerciseChapterExerciseInfo extends AbstractResource
{
    public function search(ApiRequest $request, $exerciseId)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            throw UserException::UN_LOGIN();
        }
        $moduleId = $request->query->get('moduleId', '');
        $categoryId = $request->query->get('categoryId', '');
        $this->validateParams($exerciseId, $moduleId, $categoryId);

        $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        $items = $this->getItemService()->searchItems(['bank_id' => $category['bank_id'], 'category_id' => $categoryId], [], 0, PHP_INT_MAX);
        $typesNum = $this->getItemService()->countItemTypesNum($items);
        $typesNum['total'] = $category['item_num'];

        return [
            'chapterName' => $category['name'],
            'itemCounts' => $typesNum,
        ];
    }

    private function validateParams($exerciseId, $moduleId, $categoryId)
    {
        list($exercise) = $this->getItemBankExerciseService()->tryTakeExercise($exerciseId);
        if (0 == $exercise['chapterEnable']) {
            throw ItemBankExerciseException::CHAPTER_EXERCISE_CLOSED();
        }

        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_CHAPTER != $module['type'] || $module['exerciseId'] != $exerciseId) {
            throw CommonException::ERROR_PARAMETER();
        }

        $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        if (empty($category)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        if (empty($questionBank) || $category['bank_id'] != $questionBank['itemBankId']) {
            throw ItemBankExerciseException::FORBIDDEN_TAKE_EXERCISE();
        }
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
