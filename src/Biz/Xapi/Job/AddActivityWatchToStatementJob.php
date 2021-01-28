<?php

namespace Biz\Xapi\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AddActivityWatchToStatementJob extends AbstractJob
{
    private $perCount = 5000;

    public function execute()
    {
        for ($i = 1; $i <= 10; ++$i) {
            $this->watchLogToStatement();
        }
    }

    private function watchLogToStatement()
    {
        $conditions = array(
            'is_push' => 0,
            'updated_time_LT' => time() - 60 * 60,
        );

        $orderBy = array('created_time' => 'ASC');

        $watchLogs = $this->getXapiService()->searchWatchLogs($conditions, $orderBy, 0, $this->perCount);
        if (empty($watchLogs)) {
            return;
        }

        $activityIds = ArrayToolkit::column($watchLogs, 'activity_id');
        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');
        $statements = array();
        $logIds = array();

        foreach ($watchLogs as $watchLog) {
            try {
                $activity = $activities[$watchLog['activity_id']];
                $statements[] = array(
                    'user_id' => $watchLog['user_id'],
                    'verb' => 'audio' == $activity['mediaType'] ? 'listen' : 'watch',
                    'target_id' => $watchLog['id'],
                    'target_type' => $activity['mediaType'],
                    'occur_time' => $watchLog['updated_time'],
                );
                $logIds[] = $watchLog['id'];
            } catch (\Exception $e) {
                $this->biz['logger']->error($e);
            }
        }
        if (!empty($statements)) {
            $this->getXapiService()->batchCreateStatements($statements);
            $this->getXapiService()->batchUpdateWatchLogPushed($logIds);
        }
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
