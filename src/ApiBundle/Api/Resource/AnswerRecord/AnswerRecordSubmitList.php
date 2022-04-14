<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;

class AnswerRecordSubmitList extends AbstractResource
{
    public function search(ApiRequest $request, $recordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        if (empty($answerRecord)) {
            return (object) [];
        }
        return $this->getAnswerReportService()->search(
            [
                'user_id' => $answerRecord['user_id'],
                'assessment_id' => $answerRecord['assessment_id'],
                'exclude_id' => $request->query->get('hasSelf', 0) ? -1 : $answerRecord['answer_report_id'],
            ],
            ['id' => 'ASC'],
            0,
        PHP_INT_MAX);

    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

}