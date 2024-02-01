<?php

namespace Biz\Activity\Job;

use Biz\Activity\Service\ActivityService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Traits\SyncJobErrorTrait;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteActivityJob extends AbstractJob
{
    use SyncJobErrorTrait;

    public function execute()
    {
        try {
            foreach ($this->args['ids'] as $id) {
                $this->getActivityService()->deleteActivity($id);
            }
        } catch (\Exception $e) {
            $this->innodbTrxLog($e);
            $activities = $this->getActivityService()->findActivities($this->args['ids']);
            if (empty($activities)) {
                return;
            }
            $ids = array_column($activities, 'id');
            $retry = $this->args['retry'] ?? 0;
            if ($retry < 5) {
                $this->getSchedulerService()->register([
                    'name' => "activity_delete_job_{$ids[0]}",
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 60 * $retry,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Activity\Job\DeleteActivityJob',
                    'args' => ['ids' => $ids, 'retry' => count($ids) < count($this->args['ids']) ? $retry : $retry + 1],
                ]);
            }
        }
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
