<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordReviewSingleAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $answerRecordId)
    {
        $params = $request->request->all();
        $this->validateParams($answerRecordId, $params);

        $questionReport = $this->getAnswerService()->reviewSingleAnswerByManual($answerRecordId, $params);

        $assessment = $this->getAssessmentService()->getAssessment($questionReport['assessment_id']);
        $reviewedCount = $this->getAnswerReviewedQuestionService()->countByAnswerRecordId($questionReport['answer_record_id']);
        $answerRecord = $this->getAnswerRecordService()->get($questionReport['answer_record_id']);

        if ($reviewedCount >= $assessment['question_count']) {
            $this->getAnswerService()->finishAllSingleAnswer($answerRecord, 'review');
        }

        return [
            'status' => $questionReport['status'],
            'reviewedCount' => $reviewedCount,
            'totalCount' => $assessment['question_count'],
            'isAnswerFinished' => (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) ? 1 : 0,
        ];
    }

    protected function validateParams($answerRecordId, $params)
    {
        if (empty($params['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (AnswerService::EXERCISE_MODE_SUBMIT_SINGLE != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能批阅', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw new InvalidArgumentException('assessment_id invalid.');
        }

        $sectionItem = $this->getSectionItemService()->getItemByAssessmentIdAndItemId($params['assessment_id'], $params['item_id']);
        if ($sectionItem['item_id'] != $params['item_id'] || $sectionItem['section_id'] != $params['section_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $question = $this->getItemService()->getQuestion($params['question_id']);
        if (empty($question) || $params['item_id'] != $question['item_id']) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }
}
