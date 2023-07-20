<?php

namespace AppBundle\Extensions\DataTag;

use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class BlankAssessmentAnswerRecordDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        $this->checkArguments($arguments, [
            'answerSceneId',
            'assessmentId',
            'userId',
        ]);

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($arguments['answerSceneId'], $arguments['userId']);
        if (!empty($answerRecord)) {
            return $answerRecord;
        }

        $this->getAnswerService()->batchAutoSubmit($arguments['answerSceneId'], $arguments['assessmentId'], [$arguments['userId']]);

        return $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($arguments['answerSceneId'], $arguments['userId']);
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
