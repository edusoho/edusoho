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
            'content' => $this->makeContent($item['type'], $scoreRule[$questionId]['seq'], $question),
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

    private function makeContent($type, $seq, $question)
    {
        if ('material' == $type) {
            $stem = $question['material'];
        } elseif ('fill' == $type) {
            $stem = str_replace('[[]]', '__', $question['stem']);
        } else {
            $stem = $question['stem'];
        }
        $content = "**{$this->chineseNames[$type]}**  \n{$seq}、{$stem}";
        if ('material' == $type) {
            $type = $this->modeToType[$question['answer_mode']];
            $stem = 'fill' == $type ? str_replace('[[]]', '__', $question['stem']) : $question['stem'];
            $content .= "  \n[{$this->chineseNames[$type]}]{$stem}";
        }
        if (in_array($type, ['single_choice', 'choice', 'uncertain_choice'])) {
            $responsePoints = array_column($question['response_points'], 'radio') ?: array_column($question['response_points'], 'checkbox');
            foreach ($responsePoints as $responsePoint) {
                $content .= "  \n{$responsePoint['val']}. {$responsePoint['text']}";
            }
        }

        return strip_tags($content);
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
