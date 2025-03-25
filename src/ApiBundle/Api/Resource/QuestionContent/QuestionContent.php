<?php

namespace ApiBundle\Api\Resource\QuestionContent;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Question\Traits\QuestionFlatTrait;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class QuestionContent extends AbstractResource
{
    use QuestionFlatTrait;

    public function get(ApiRequest $request, $itemId)
    {
        $this->check($request->query->get(''), $itemId);
        $item = $this->getItemService()->getItemWithQuestions($itemId, true);

        return ['content' => $this->flatten($item)];
    }

    private function check($answerRecordId, $itemId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
    }

    /**
     * @return AnswerRecordService
     */
    private function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ItemService
     */
    private function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
