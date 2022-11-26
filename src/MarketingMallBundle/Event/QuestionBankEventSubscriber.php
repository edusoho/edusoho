<?php

namespace MarketingMallBundle\Event;

use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;
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
            'questionBankProduct.update' => 'onQuestionBankUpdate',
            'questionBankProduct.delete' => 'onQuestionBankProductDelete'
        ];
    }

    public function onItemCategoryCreate(Event $event)
    {
        $exercise = $this->getExerciseService()->getByQuestionBankId($event->getArgument('bankId'));
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }

    }

    public function onItemCategoryUpdate(Event $event)
    {
        $category = $event->getSubject();
        $fields = $event->getArgument('fields');

        if ($category['name'] == $fields['name']) {
            return;
        }
        $exercise = $this->getExerciseService()->getByQuestionBankId($category['bank_id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onItemCategoryDelete(Event $event)
    {
        $category = $event->getSubject();
        $exercise = $this->getExerciseService()->getByQuestionBankId($category['bank_id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onAssessmentExerciseCreate(Event $event)
    {
        $this->syncQuestionBankToMarketingMall($event->getArgument('exerciseId'));
    }

    public function onAssessmentExerciseDelete(Event $event)
    {
        $assessmentExercise = $event->getSubject();
        $this->syncQuestionBankToMarketingMall($assessmentExercise['exerciseId']);
    }

    public function onExerciseAssessmentModuleCreate(Event $event)
    {
        $this->syncQuestionBankToMarketingMall($event->getArgument('exerciseId'));
    }

    public function onExerciseAssessmentModuleUpdate(Event $event)
    {
        $module = $event->getSubject();
        $fields = $event->getArgument('fields');
        if ($module['title'] != $fields['title']) {
            $this->syncQuestionBankToMarketingMall($module['exerciseId']);
        }
    }

    public function onExerciseAssessmentModuleDelete(Event $event)
    {
        $module = $event->getSubject();
        $this->syncQuestionBankToMarketingMall($module['exerciseId']);
    }

    public function onItemUpdateCategory(Event $event)
    {
        $categoryId = $event->getArgument('categoryId');
        $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($category['bank_id']);
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onItemCreate(Event $event)
    {
        $item = $event->getSubject();

//        if (empty($item['category_id'])) {
//            return;
//        }
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onItemUpdate(Event $event)
    {
        $item = $event->getSubject();
        $originItem = $event->getArgument('originItem');
        if ($originItem['category_id'] == $item['category_id']) {
            return;
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        if (empty($item['category_id'])) {
            return;
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onItemBatchDelete(Event $event)
    {
        $deleteItems = $event->getSubject();
        foreach ($deleteItems as $item) {
            if (empty($item['category_id'])) {
                continue;
            }
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
            $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
            if ($exercise) {
                $this->syncQuestionBankToMarketingMall($exercise['id']);
            }
            break;
        }
    }

    public function onItemImport(Event $event)
    {
        $items = $event->getSubject();
        foreach ($items as $item) {
            if (empty($item['category_id'])) {
                continue;
            }
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
            $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
            if ($exercise) {
                $this->syncQuestionBankToMarketingMall($exercise['id']);
            }
            break;
        }
    }

    public function onQuestionBankUpdate(Event $event)
    {
        $questionBankId = $event->getSubject()['questionBankId'];
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBankId);
        if ($exercise) {
            $this->syncQuestionBankToMarketingMall($exercise['id']);
        }
    }

    public function onQuestionBankProductDelete(Event $event)
    {
        $exercise = $event->getSubject();
        $this->deleteQuestionBankProductToMarketingMall($exercise['id']);
    }

    protected function syncQuestionBankToMarketingMall($exerciseId)
    {
        $data = $this->getSyncListService()->getSyncDataId($exerciseId);
        foreach ($data as $value) {
            if($value['id'] && $value['type'] == 'questionBank' && $value['status'] == 'new') {
                return;
            }
        }

        $this->getSyncListService()->addSyncList(['type' => 'questionBank', 'data' => $exerciseId]);
    }

    protected function deleteQuestionBankProductToMarketingMall($questionBankId)
    {
        $relation = $this->getProductMallGoodsRelationService()->getProductMallGoodsRelationByProductTypeAndProductId('questionBank', $questionBankId);
        if ($relation) {
            $this->getProductMallGoodsRelationService()->deleteProductMallGoodsRelation($relation['id']);
            $this->deleteMallGoods($relation['goodsCode']);
        }
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

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->getBiz()->service('MarketingMallBundle:SyncList:SyncListService');
    }
}
