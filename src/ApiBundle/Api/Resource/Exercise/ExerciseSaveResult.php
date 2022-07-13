<?php

namespace ApiBundle\Api\Resource\Exercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Testpaper\ExerciseException;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class ExerciseSaveResult extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseResultId)
    {
        $exerciseRecord = $this->getAnswerRecordService()->get($exerciseResultId);

        if (!empty($exerciseRecord) && !in_array($exerciseRecord['status'], ['doing', 'paused'])) {
            throw ExerciseException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        if ($exerciseRecord['user_id'] != $this->getCurrentUser()->getId()) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        $wrapper = new AssessmentResponseWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($exerciseRecord['assessment_id']);
        $assessmentResponse = $wrapper->wrap($request->request->all(), $assessment, $exerciseRecord);

        return $this->getAnswerService()->saveAnswer($assessmentResponse);
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
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
