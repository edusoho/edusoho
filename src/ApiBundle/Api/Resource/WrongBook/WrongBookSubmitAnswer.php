<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Testpaper\ExerciseException;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class WrongBookSubmitAnswer extends AbstractResource
{
    public function update(ApiRequest $request, $poolId, $exerciseRecordId)
    {
        $user = $this->getCurrentUser();
        $assessmentResponse = $request->request->all();
        $exerciseRecord = $this->getAnswerRecordService()->get($exerciseRecordId);

        if ($exerciseRecord['user_id'] != $user['id']) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        $assessment = $this->getAssessmentService()->showAssessment($exerciseRecord['assessment_id']);
        $answerRecord = $this->getAnswerService()->submitAnswer($assessmentResponse);
        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $scene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $testpaperWrapper = new TestpaperWrapper();

        return $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $scene, $answerReport);
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
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }
}
