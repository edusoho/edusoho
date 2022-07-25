<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\LogService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class AnswerRecordFillAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $recordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        if (empty($answerRecord)) {
            return false;
        }
        $answerData = $request->request->all();
        if (empty($answerData)) {
            return false;
        }
        $result = $this->getAnswerService()->reviseFillAnswer($recordId, $answerData);
        if($result){
            $this->getLogService()->info('course', 'revise-fill-answer', "修改了学员填空题得分", ['answerRecordId'=>$recordId, 'userId' => $this->getCurrentUser()->getId(),'data'=>$answerData]);
        }

        return true;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->service('System:LogService');
    }

    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerService
     */
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
