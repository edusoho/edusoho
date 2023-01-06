<?php

namespace ApiBundle\Api\Resource\PauseAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\ErrorCode;

class PauseAnswer extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $assessmentResponse = $request->request->all();
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }
        if (!$this->getExerciseMemberService()->isExerciseMemberByAssessmentId($assessmentResponse['assessment_id'], $this->getCurrentUser()->getId())){
            throw new AnswerException("您已退出题库，无法继续学习", ErrorCode::NOT_ITEM_BANK_MEMBER);
        }
        return $this->getAnswerService()->pauseAnswer($assessmentResponse);
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
