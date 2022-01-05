<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class AnswerRecordComment extends AbstractResource
{
    public function add(ApiRequest $request, $recordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        if (empty($answerRecord)) {
            return (object) [];
        }
        $comment = $request->request->get('comment', '');
        $answerReport = $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['comment' => $comment]);

        return $answerReport;
    }

    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
