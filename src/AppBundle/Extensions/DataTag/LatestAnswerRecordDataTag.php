<?php

namespace AppBundle\Extensions\DataTag;

use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class LatestAnswerRecordDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        $user = $this->getCurrentUser();

        $this->checkArguments($arguments, [
            'answerSceneId',
        ]);

        return $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($arguments['answerSceneId'], $user['id']);
    }

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
