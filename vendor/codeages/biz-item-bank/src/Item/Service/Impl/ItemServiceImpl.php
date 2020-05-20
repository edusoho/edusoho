<?php

namespace Codeages\Biz\ItemBank\Item\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Type\Item;
use Codeages\Biz\ItemBank\Item\Wrapper\ExportItemsWrapper;
use Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;
use ExamParser\Writer\WriteDocx;
use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;

class ItemServiceImpl extends BaseService implements ItemService
{
    public function createItem($item)
    {
        if (empty($item['type'])) {
            throw new ItemException('Item without type', ErrorCode::ITEM_ARGUMENT_INVALID);
        }
        $arguments = $item;
        $item = $this->getItemProcessor($item['type'])->process($item);
        $item['created_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];
        $item['updated_user_id'] = $item['created_user_id'];
        $questions = $item['questions'];
        unset($item['questions']);

        $this->beginTransaction();
        try {
            $item = $this->getItemDao()->create($item);
            if (!empty($arguments['attachments'])) {
                $this->updateAttachments($arguments['attachments'], $item['id'], AttachmentService::ITEM_TYPE);
            }

            $this->createQuestions($item['id'], $questions);

            $this->getItemBankService()->updateItemNum($item['bank_id'], 1);

            $this->dispatch('item.create', $item, ['argument' => $arguments]);

            $this->commit();

            return $item;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function importItems($items, $bankId)
    {
        $savedItems = [];
        $groupItems = ArrayToolkit::group($items, 'type');

        try {
            $this->beginTransaction();
            foreach ($groupItems as $group) {
                foreach ($group as $item) {
                    $item['bank_id'] = $bankId;
                    $savedItem = $this->createItem($item);
                    $savedItems[] = array_merge($savedItems, $savedItem);
                }
            }
            $this->commit();

            return $savedItems;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function readWordFile($wordPath, $resourcePath = '')
    {
        $options = [];
        if (!empty($resourcePath)) {
            $options = ['resourceTmpPath' => $resourcePath];
        }
        $parser = $this->biz['item_parser'];

        return $parser->read($wordPath, $options);
    }

    public function parseItems($text)
    {
        $parser = $this->biz['item_parser'];

        return $parser->parse($text);
    }

    public function updateItem($id, $item)
    {
        $originItem = $this->getItem($id);
        if (empty($originItem)) {
            throw new ItemException('Item not found', ErrorCode::ITEM_NOT_FOUND);
        }
        $arguments = $item;
        $item = $this->getItemProcessor($originItem['type'])->process($item);
        $item['updated_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];
        $questions = $item['questions'];
        unset($item['questions']);

        $this->beginTransaction();
        try {
            $this->updateQuestions($id, $questions);

            $item['question_num'] = $this->getQuestionDao()->count(['item_id' => $id]);
            $item = $this->getItemDao()->update($id, $item);
            if (!empty($arguments['attachments'])) {
                $this->updateAttachments($arguments['attachments'], $id, AttachmentService::ITEM_TYPE);
            }

            $this->dispatch('item.update', $item, ['argument' => $arguments]);
            $this->commit();

            return $item;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getItem($id)
    {
        return $this->getItemDao()->get($id);
    }

    public function getItemWithQuestions($id, $withAnswer = false)
    {
        $item = $this->getItem($id);
        if (empty($item)) {
            return [];
        }

        $item['questions'] = $this->findQuestionsByItemId($item['id']);
        $item = $this->biz['item_wrapper']->wrap($item, $withAnswer);
        $item = $this->biz['item_attachment_wrapper']->wrap($item);

        return $item;
    }

    public function findItemsByIds($ids, $withQuestions = false)
    {
        $items = $this->getItemDao()->findByIds($ids);
        if ($withQuestions) {
            $questions = $this->findQuestionsByItemIds(ArrayToolkit::column($items, 'id'));
            $questions = ArrayToolkit::group($questions, 'item_id');
            foreach ($items as &$item) {
                $item['questions'] = empty($questions[$item['id']]) ? [] : $questions[$item['id']];
            }
        }
        $that = $this;
        array_walk($items, function (&$item) use ($that) {
            $item['includeImg'] = $that->hasImg($item['material']);
            $item = $this->biz['item_attachment_wrapper']->wrap($item);
        });

        return ArrayToolkit::index($items, 'id');
    }

    public function searchItems($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->filterItemConditions($conditions);

        $items = $this->getItemDao()->search($conditions, $orderBys, $start, $limit, $columns);

        if (empty($columns) || in_array('material', $columns)) {
            $that = $this;
            array_walk($items, function (&$item) use ($that) {
                $item['includeImg'] = $that->hasImg($item['material']);
            });
        }

        return $items;
    }

    public function countItems($conditions)
    {
        $conditions = $this->filterItemConditions($conditions);

        return $this->getItemDao()->count($conditions);
    }

    public function getItemCountGroupByTypes($conditions)
    {
        $conditions = $this->filterItemConditions($conditions);

        return $this->getItemDao()->getItemCountGroupByTypes($conditions);
    }

    public function findItemsByCategoryIds($categoryIds)
    {
        return $this->getItemDao()->findByCategoryIds($categoryIds);
    }

    public function deleteItem($id)
    {
        $item = $this->getItem($id);
        if (empty($item)) {
            return false;
        }
        try {
            $this->beginTransaction();

            $result = $this->getItemDao()->delete($id);
            $this->getAttachmentService()->batchDeleteAttachment(['target_id' => $id, 'target_type' => 'item']);
            $this->deleteQuestions(['item_id' => $id]);
            $this->getItemBankService()->updateItemNum($item['bank_id'], -1);
            $this->dispatch('item.delete', $item);

            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteItems($ids)
    {
        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            $this->deleteItem($id);
        }

        return true;
    }

    public function updateItemsCategoryId($ids, $categoryId)
    {
        if (empty($ids)) {
            return [];
        }

        $updateFields = [];
        foreach ($ids as $id) {
            $updateFields[] = ['category_id' => $categoryId];
        }

        return $this->getItemDao()->batchUpdate($ids, $updateFields, 'id');
    }

    public function review($itemResponses)
    {
        $reviewResults = [];

        $items = $this->getItemDao()->findByIds(array_column($itemResponses, 'item_id'));
        $items = ArrayToolkit::index($items, 'id');
        foreach ($itemResponses as $itemResponse) {
            $itemType = empty($items[$itemResponse['item_id']]['type']) ? ChoiceItem::TYPE : $items[$itemResponse['item_id']]['type'];
            $reviewResults[] = $this->getItemProcessor($itemType)->review(
                $itemResponse['item_id'],
                $itemResponse['question_responses']
            );
        }

        return $reviewResults;
    }

    public function exportItems($bankId, $conditions, $path, $imgRootDir)
    {
        if (empty($this->getItemBankService()->getItemBank($bankId))) {
            throw new ItemBankException('Item bank not found.', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        $conditions['bank_id'] = $bankId;
        $items = $this->searchItems($conditions, ['created_time' => 'DESC'], 0, $this->countItems($conditions));
        if (empty($items)) {
            return false;
        }
        $items = $this->getExportItemsWrapper($imgRootDir)->wrap($items);

        $writer = new WriteDocx($path);
        $writer->write($items);

        return true;
    }

    public function findQuestionsByQuestionIds($questionIds)
    {
        $questions = $this->getQuestionDao()->findQuestionsByQuestionIds($questionIds);

        return ArrayToolkit::index($questions, 'id');
    }

    protected function createQuestions($itemId, $questions)
    {
        if (empty($questions)) {
            return;
        }
        foreach ($questions as $question) {
            $question['item_id'] = $itemId;
            $question['created_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];
            $question['updated_user_id'] = $question['created_user_id'];
            $attachments = $question['attachments'];
            unset($question['attachments']);
            $itemQuestion = $this->getQuestionDao()->create($question);
            if (!empty($attachments)) {
                $this->updateAttachments($attachments, $itemQuestion['id'], AttachmentService::QUESTION_TYPE);
            }
        }
    }

    protected function updateQuestions($itemId, $questions)
    {
        if (empty($questions)) {
            return;
        }
        $originQuestionIds = array_column($this->findQuestionsByItemId($itemId), 'id');
        $updateQuestions = [];
        $questionAttachments = [];
        foreach ($questions as $key => $question) {
            if (empty($question['id'])) {
                continue;
            }
            if (in_array($question['id'], $originQuestionIds)) {
                $question['updated_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];

                $questionAttachments[] = ['id' => $question['id'], 'attachments' => $question['attachments']];
                unset($question['attachments']);

                $updateQuestions[] = $question;
                unset($questions[$key]);
            }
        }
        $this->createQuestions($itemId, $questions);
        $updateQuestionIds = array_column($updateQuestions, 'id');
        if (!empty($updateQuestionIds)) {
            $this->getQuestionDao()->batchUpdate($updateQuestionIds, $updateQuestions);
            $this->updateQuestionAttachments($questionAttachments);
        }
        $deleteQuestions = array_diff($originQuestionIds, $updateQuestionIds);
        if (!empty($deleteQuestions)) {
            $this->deleteQuestions(['ids' => $deleteQuestions]);
        }
    }

    protected function updateQuestionAttachments($questions)
    {
        foreach ($questions as $question) {
            if (!empty($question['attachments'])) {
                $this->updateAttachments($question['attachments'], $question['id'], AttachmentService::QUESTION_TYPE);
            }
        }
    }

    protected function updateAttachments($attachments, $targetId, $targetType)
    {
        foreach ($attachments as $attachment) {
            $this->getAttachmentService()->updateAttachment($attachment['id'], [
                'target_id' => $targetId,
                'target_type' => $targetType,
                'module' => $attachment['module'],
            ]);
        }
    }

    protected function deleteQuestions($conditions)
    {
        $questionCount = $this->getQuestionDao()->count($conditions);
        $questions = $this->getQuestionDao()->search($conditions, [], 0, $questionCount);

        $result = $this->getQuestionDao()->batchDelete($conditions);
        $this->getAttachmentService()->batchDeleteAttachment(['target_ids' => ArrayToolkit::column($questions, 'id'), 'target_type' => 'question']);

        return $result;
    }

    protected function findQuestionsByItemId($itemId)
    {
        return $this->getQuestionDao()->findByItemId($itemId);
    }

    public function findQuestionsByItemIds($itemIds)
    {
        return $this->getQuestionDao()->findByItemsIds($itemIds);
    }

    protected function filterItemConditions($conditions)
    {
        if (!empty($conditions['keyword'])) {
            $conditions['material'] = '%'.trim($conditions['keyword']).'%';
            unset($conditions['keyword']);
        }

        return $conditions;
    }

    protected function hasImg($text)
    {
        if (preg_match('/<img (.*?)>/', $text)) {
            return true;
        }

        return false;
    }

    /**
     * @param $imgRootDir
     *
     * @return ExportItemsWrapper
     */
    protected function getExportItemsWrapper($imgRootDir)
    {
        $exportItemsWrapper = $this->biz['export_items_wrapper'];
        $exportItemsWrapper->setImgRootDir($imgRootDir);

        return $exportItemsWrapper;
    }

    /**
     * @param $type
     *
     * @return Item
     */
    protected function getItemProcessor($type)
    {
        return $this->biz['item_type_factory']->create($type);
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->biz->service('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }
}
