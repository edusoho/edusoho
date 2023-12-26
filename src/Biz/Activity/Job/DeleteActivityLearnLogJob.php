<?php

namespace Biz\Activity\Job;

use Biz\Activity\Dao\ActivityLearnLogDao;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Traits\SyncJobErrorTrait;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class DeleteActivityLearnLogJob extends AbstractJob
{
    use SyncJobErrorTrait;

    public function execute()
    {
        $activityId = $this->args['activityId'];
        $limit = 5000;
        try {
            $deletedCount = $this->getActivityLearnLogDao()->deleteLimitByActivityId($activityId, $limit);
            if ($deletedCount == $limit) {
                $this->registerNextJob($activityId);
            }
        } catch (\Exception $e) {
            $this->innodbTrxLog($e);
            $this->registerNextJob($activityId);
        }
    }

    private function registerNextJob($activityId)
    {
        $this->getSchedulerService()->register([
            'name' => "activity_learn_log_delete_job_{$activityId}",
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Activity\Job\DeleteActivityLearnLogJob',
            'args' => ['activityId' => $activityId],
        ]);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityLearnLogDao
     */
    private function getActivityLearnLogDao()
    {
        return $this->biz->dao('Activity:ActivityLearnLogDao');
    }
}
