<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\ErrorCode;

class AnswerRecordFinishAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $answerRecordId)
    {
        $this->validateParams($answerRecordId);

        return $this->getAnswerService()->finishAnswer($answerRecordId);
    }

    public function validateParams($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (ExerciseMode::SUBMIT_SINGLE != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能结束答题', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
            throw new AnswerException('答题已结束', ErrorCode::ANSWER_FINISHED);
        }
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }
}
