<?php

namespace Biz\Testpaper\Job;

use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Codeages\Biz\Framework\Queue\AbstractJob;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use PhpOffice\PhpWord\Exception\Exception;

class AssessmentAutoSubmitJob extends AbstractJob
{
    public function execute()
    {
        try {
            file_put_contents('/tmp/test','1');
            $record = $this->getAnswerRecordService()->get($this->args['answerRecordId']);
            if (empty($record) || $record['status'] == 'finished') {
                return;
            }
            $assessment = $this->getAttachmentService()->getAttachment($record['assessment_id']);
            $answerScene = $this->getAnswerSceneService()->get($record['answer_scene_id']);
            $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($record['id']);
            $wrapper = new AssessmentResponseWrapper();
            $response = $wrapper->wrap(['data' => $questionReports], $assessment, $record);
            $response['used_time'] = $answerScene['limited_time'];
            $this->getAnswerService()->submitAnswer($response);
        } catch (Exception $e) {
            file_put_contents('/tmp/test', json_encode($e->getMessage()));
        }
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }
}