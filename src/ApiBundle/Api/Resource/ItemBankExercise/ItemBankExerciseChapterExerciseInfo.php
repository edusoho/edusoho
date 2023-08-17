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

class ItemBankExerciseChapterExerciseInfo extends AbstractResource
{
    public function search(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
        }
        list($exercise, $member) = $this->getItemBankExerciseService()->tryTakeExercise($exerciseId);

        $moduleId = $request->query->get('moduleId', '');
        $categoryId = $request->query->get('categoryId', '');

        $this->canLearnExercise($moduleId, $exerciseId);

        $category = $this->getItemCategoryService()->getItemCategory($categoryId);

        if (empty($category) || empty($category['question_num'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        if ($category['bank_id'] != $questionBank['id']) {
            throw ItemBankExerciseException::FORBIDDEN_TAKE_EXERCISE();
        }

        return [
            'chapterName' => $category['name'],
            'itemNums' => $category['item_num'],
        ];
    }

    public function canLearnExercise($moduleId, $exerciseId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_CHAPTER != $module['type']) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if ($module['exerciseId'] != $exerciseId) {
            throw ItemBankExerciseException::FORBIDDEN_TAKE_EXERCISE();
        }

        if (!$this->getItemBankExerciseService()->canTakeItemBankExercise($module['exerciseId'])) {
            throw ItemBankExerciseException::FORBIDDEN_TAKE_EXERCISE();
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($module['exerciseId']);
        if (0 == $itemBankExercise['chapterEnable']) {
            throw ItemBankExerciseException::CHAPTER_EXERCISE_CLOSED();
        }
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }
}
