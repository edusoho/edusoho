<?php

namespace AppBundle\Controller\Testpaper;

use AppBundle\Controller\BaseController;
use Biz\Testpaper\Job\QuestionItemAnalysisJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Symfony\Component\HttpFoundation\Request;

class BaseTestpaperController extends BaseController
{
    const SYNC_ANALYSIS_THRESHOLD = 15000;

    const SYNC_ANALYSIS_TIME_THRESHOLD = 28800;

    public function syncJobAction(Request $request, $answerSceneId)
    {
        $job = $this->getSceneAnalysisJob($answerSceneId);

        if (empty($job)) {
            $job = $this->registerSceneAnalysisJob($answerSceneId);
        }
        $answerScene = $this->getAnswerSceneService()->get($answerSceneId);
        $this->getAnswerSceneService()->update($answerSceneId, ['name' => $answerScene['name'], 'question_report_job_name' => $job['name']]);

        return $this->createJsonResponse(true);
    }

    public function jobCheckAction(Request $request, $answerSceneId)
    {
        $answerScene = $this->getAnswerSceneService()->get($answerSceneId);
        $jobName = $answerScene['question_report_job_name'];
        $jobFired = $this->getSchedulerService()->findJobFiredByJobName($jobName);
        if (empty($jobFired)) {
            return $this->createJsonResponse(false);
        }
        if ('success' === $jobFired[0]['status']) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    protected function needSyncJob($answerCount, $questionNum)
    {
        $threshold = $this->setting('magic.biz_answer_scene_report_threshold', self::SYNC_ANALYSIS_THRESHOLD);

        return $answerCount * $questionNum > $threshold;
    }

    protected function registerSceneAnalysisJob($sceneId)
    {
        $updateRealTimeTestResultStatusJob = [
            'name' => 'question_item_analysis_'.$sceneId.'_'.time(),
            'expression' => time(),
            'class' => QuestionItemAnalysisJob::class,
            'args' => [
                'sceneId' => $sceneId,
            ],
        ];

        return $this->getSchedulerService()->register($updateRealTimeTestResultStatusJob);
    }

    protected function getSceneAnalysisJob($sceneId)
    {
        $scene = $this->getAnswerSceneService()->get($sceneId);
        $this->getSchedulerService()->countJobFires(['job_name' => $scene['question_report_job_name'], 'status' => '']);

        return $this->getSchedulerService()->getJobByName($scene['question_report_job_name']);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }
}
