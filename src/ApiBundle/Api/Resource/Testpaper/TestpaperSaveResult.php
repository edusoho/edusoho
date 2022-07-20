<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Testpaper\TestpaperException;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class TestpaperSaveResult extends AbstractResource
{
    public function add(ApiRequest $request, $resultId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $testpaperRecord = $this->getAnswerRecordService()->get($resultId);
        if ($testpaperRecord['user_id'] != $user['id']) {
            throw TestpaperException::FORBIDDEN_ACCESS_TESTPAPER();
        }

        if ($testpaperRecord && !in_array($testpaperRecord['status'], ['doing', 'paused'])) {
            throw TestpaperException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        $wrapper = new AssessmentResponseWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($testpaperRecord['assessment_id']);
        $assessmentResponse = $wrapper->wrap($request->request->all(), $assessment, $testpaperRecord);

        return $this->getAnswerService()->saveAnswer($assessmentResponse);
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
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }
}
