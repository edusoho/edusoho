<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordSubmitSingleAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $answerRecordId)
    {
        $params = $request->request->all();
        $this->validateParams($answerRecordId, $params);
        $params = $this->trimResponse($params);

        $questionReport = $this->getAnswerService()->submitSingleAnswer($answerRecordId, $params);

        $assessment = $this->getAssessmentService()->getAssessment($questionReport['assessment_id']);
        $reviewedCount = $this->getAnswerReviewedQuestionService()->countReviewedByAnswerRecordId($questionReport['answer_record_id']);
        $answerRecord = $this->getAnswerRecordService()->get($questionReport['answer_record_id']);

        if ($reviewedCount >= $assessment['question_count']) {
            $this->getAnswerService()->finishAllSingleAnswer($answerRecord, 'submit');
        }

        $item = $this->getItemService()->getItem($questionReport['item_id']);
        $question = $this->getItemService()->getQuestion($questionReport['question_id']);

        return [
            'answer' => $question['answer'],
            'itemAnalysis' => $item['analysis'],
            'questionAnalysis' => $question['analysis'],
            'status' => $questionReport['status'],
            'manualMarking' => empty($questionReport['isReviewed']) ? 1 : 0,
            'reviewedCount' => $reviewedCount,
            'totalCount' => $assessment['question_count'],
            'isAnswerFinished' => (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) ? 1 : 0,
        ];
    }

    public function validateParams($answerRecordId, $params)
    {
        if (empty($params['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (AnswerService::EXERCISE_MODE_SUBMIT_SINGLE != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能保存', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw new InvalidArgumentException('assessment_id invalid.');
        }

        $sectionItem = $this->getSectionItemService()->getItemByAssessmentIdAndItemId($params['assessment_id'], $params['item_id']);
        if (empty($sectionItem) || $sectionItem['section_id'] != $params['section_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $question = $this->getItemService()->getQuestion($params['question_id']);
        if (empty($question) || $params['item_id'] != $question['item_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        if ($answerRecord['admission_ticket'] != $params['admission_ticket']) {
            throw new AnswerException('有新答题页面，请在新页面中继续答题', ErrorCode::ANSWER_NO_BOTH_DOING);
        }

        if (!in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_DOING, AnswerService::ANSWER_RECORD_STATUS_PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }
    }

    protected function trimResponse($params)
    {
        foreach ($params['response'] as &$response) {
            $response = trim($response);
        }

        return $params;
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->service('ItemBank:Assessment:AssessmentSectionItemService');
    }
}
