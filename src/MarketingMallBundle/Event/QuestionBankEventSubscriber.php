<?php

namespace MarketingMallBundle\Event;

use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use MarketingMallBundle\Common\GoodsContentBuilder\QuestionBankBuilder;

class QuestionBankEventSubscriber extends BaseEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'itemCategory.create' => 'onItemCategoryCreate',
            'itemCategory.update' => 'onItemCategoryUpdate',
            'itemCategory.delete' => 'onItemCategoryDelete',
            'assessmentExercise.create' => 'onAssessmentExerciseCreate',
            'assessmentExercise.delete' => 'onAssessmentExerciseDelete',
            'exercise.assessmentModule.create' => 'onExerciseAssessmentModuleCreate',
            'exercise.assessmentModule.update' => 'onExerciseAssessmentModuleUpdate',
            'exercise.assessmentModule.delete' => 'onExerciseAssessmentModuleDelete',
            'item.update_category' => 'onItemUpdateCategory',
            'item.create' => 'onItemCreate',
            'item.update' => 'onItemUpdate',
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
            'item.import' => 'onItemImport',
        ];
    }

    public function onItemCategoryCreate(Event $event)
    {
        $bankId = $event->getArgument('bankId');
        $this->syncQuestionBankToMarketingMall($bankId);
    }

    public function onItemCategoryUpdate(Event $event)
    {
        $category = $event->getSubject();
        $fields = $event->getArgument('fields');
        if ($category['name'] != $fields['name']) {
            $this->syncQuestionBankToMarketingMall($category['bank_id']);
        }
    }

    public function onItemCategoryDelete(Event $event)
    {
        $category = $event->getSubject();
        $this->syncQuestionBankToMarketingMall($category['bank_id']);
    }

    public function onAssessmentExerciseCreate(Event $event)
    {
        $exerciseId = $event->getArgument('exerciseId');
        $exercise = $this->getExerciseService()->get($exerciseId);
        $this->syncQuestionBankToMarketingMall($exercise['questionBankId']);
    }

    public function onAssessmentExerciseDelete(Event $event)
    {
        $assessmentExercise = $event->getSubject();
        $exercise = $this->getExerciseService()->get($assessmentExercise['exerciseId']);
        $this->syncQuestionBankToMarketingMall($exercise['questionBankId']);
    }

    public function onExerciseAssessmentModuleCreate(Event $event)
    {
        $exerciseId = $event->getArgument('exerciseId');
        $exercise = $this->getExerciseService()->get($exerciseId);
        $this->syncQuestionBankToMarketingMall($exercise['questionBankId']);
    }

    public function onExerciseAssessmentModuleUpdate(Event $event)
    {
        $module = $event->getSubject();
        $fields = $event->getArgument('fields');
        if ($module['title'] != $fields['title']) {
            $exercise = $this->getExerciseService()->get($module['exerciseId']);
            $this->syncQuestionBankToMarketingMall($exercise['questionBankId']);
        }
    }

    public function onExerciseAssessmentModuleDelete(Event $event)
    {
        $module = $event->getSubject();
        $exercise = $this->getExerciseService()->get($module['exerciseId']);
        $this->syncQuestionBankToMarketingMall($exercise['questionBankId']);
    }

    public function onItemUpdateCategory(Event $event)
    {
        $categoryId = $event->getArgument('categoryId');
        $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($category['bank_id']);
        $this->syncQuestionBankToMarketingMall($questionBank['id']);
    }

    public function onItemCreate(Event $event)
    {
        $item = $event->getSubject();
        if (!empty($item['category_id'])) {
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
            $this->syncQuestionBankToMarketingMall($questionBank['id']);
        }
    }

    public function onItemUpdate(Event $event)
    {
        $item = $event->getSubject();
        $originItem = $event->getArgument('originItem');
        if ($originItem['category_id'] != $item['category_id']) {
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
            $this->syncQuestionBankToMarketingMall($questionBank['id']);
        }
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        if (!empty($item['category_id'])) {
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
            $this->syncQuestionBankToMarketingMall($questionBank['id']);
        }
    }

    public function onItemBatchDelete(Event $event)
    {
        $deleteItems = $event->getSubject();
        foreach ($deleteItems as $item) {
            if (!empty($item['category_id'])) {
                $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
                $this->syncQuestionBankToMarketingMall($questionBank['id']);
                break;
            }
        }
    }

    public function onItemImport(Event $event)
    {
        $items = $event->getSubject();
        foreach ($items as $item) {
            if (!empty($item['category_id'])) {
                $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
                $this->syncQuestionBankToMarketingMall($questionBank['id']);
                break;
            }
        }
    }

    protected function syncQuestionBankToMarketingMall($questionBankId)
    {
        $this->updateGoodsContent('question_bank', new QuestionBankBuilder(), $questionBankId);
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }
}
