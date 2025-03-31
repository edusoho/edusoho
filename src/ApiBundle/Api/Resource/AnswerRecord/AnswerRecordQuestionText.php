<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Question\Traits\QuestionFlatTrait;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordQuestionText extends AbstractResource
{
    use QuestionFlatTrait;

    public function get(ApiRequest $request, $answerRecordId, $questionId)
    {
        $assessmentItem = $this->getAssessmentItem($answerRecordId, $questionId);
        if (empty($assessmentItem)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $question = $this->getItemService()->getQuestion($questionId);
        $item = $this->getItemService()->getItem($question['item_id']);
        $question['material'] = $item['material'];
        $scoreRule = array_column($assessmentItem['score_rule'], null, 'question_id');

        return [
            'content' => "{$scoreRule[$questionId]['seq']}、{$this->flattenMain($item['type'], $question)}",
            'question' => "{$this->flattenMain($item['type'], $question)}{$this->flattenAnswer($item['type'], $question)}{$this->flattenAnalysis($question)}",
        ];
    }

    private function getAssessmentItem($answerRecordId, $questionId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord) || ($this->getCurrentUser()->getId() != $answerRecord['user_id'])) {
            return false;
        }
        $question = $this->getItemService()->getQuestionIncludeDeleted($questionId);
        if (empty($question)) {
            return false;
        }
        $item = $this->getItemService()->getItemIncludeDeleted($question['item_id']);
        if (empty($item)) {
            return false;
        }

        return $this->getSectionItemService()->getItemByAssessmentIdAndItemId($answerRecord['assessment_id'], $item['id']);
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

    /**
     * @return AssessmentSectionItemService
     */
    private function getSectionItemService()
    {
        return $this->service('ItemBank:Assessment:AssessmentSectionItemService');
    }
}
