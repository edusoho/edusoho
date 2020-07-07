<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Biz\Common\CommonException;

class AnswerRecord extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $answerRecord = $this->getAnswerRecordService()->get($id);
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (empty($answerRecord['answer_report_id'])) {
            return (object) [];
        }

        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $answerReportFilter = new AnswerReportFilter();
        $answerReportFilter->filter($answerReport);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return [
            'answer_report' => $answerReport,
            'answer_record' => $answerRecord,
            'assessment' => $assessment,
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
        ];

        return $this->getAnswerReportService()->get($id);
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
}
