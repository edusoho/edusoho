<?php

namespace Biz\Testpaper\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class QuestionItemAnalysisJob extends AbstractJob
{
    public function execute()
    {
        if (empty($this->args['sceneId'])) {
            return;
        }
        $this->getAnswerSceneService()->buildAnswerSceneReport($this->args['sceneId']);
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }
}
