<?php

namespace Biz\Testpaper\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class BatchQuestionItemAnalysisJob extends AbstractJob
{
    public function execute()
    {
        $scenes = $this->getAnswerSceneService()->findNotStatisticsQuestionsReportScenes(100);
        foreach ($scenes as $scene) {
            $this->getAnswerSceneService()->buildAnswerSceneReport($scene['id']);
        }
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }
}
