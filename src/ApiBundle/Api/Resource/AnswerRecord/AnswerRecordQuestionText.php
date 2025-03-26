<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Question\Traits\QuestionFlatTrait;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordQuestionText extends AbstractResource
{
    use QuestionFlatTrait;

    public function get(ApiRequest $request, $answerRecordId, $questionId)
    {
        $this->check($answerRecordId, $questionId);
        $question = $this->getItemService()->getQuestion($questionId);
        $item = $this->getItemService()->getItem($question['item_id']);
        $question['material'] = $item['material'];

        return [
            'content' => $this->flattenMain($item['type'], $question),
            'question' => "{$this->flattenMain($item['type'], $question)}{$this->flattenAnswer($item['type'], $question)}{$this->flattenAnalysis($question)}",
        ];
    }

    private function check($answerRecordId, $questionId)
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
