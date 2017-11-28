<?php

namespace Biz\Xapi\Job;

use Biz\Activity\Service\ActivityService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AddActivityWatchToStatementJob extends AbstractJob
{
    public function execute()
    {
        $conditions = array(
            'is_push' => 0,
            'updated_time_LT' => time() - 60 * 60,
        );

        $orderBy = array('created_time' => 'ASC');

        $watchLogs = $this->getXapiService()->searchWatchLogs($conditions, $orderBy, 0, 10);

        foreach ($watchLogs as $watchLog) {
            $activity = $this->getActivityService()->getActivity($watchLog['activity_id']);
            $statement = array(
                'user_id' => $watchLog['user_id'],
                'verb' => 'audio' == $activity['mediaType'] ? 'listen' : 'watch',
                'target_id' => $watchLog['id'],
                'target_type' => $activity['mediaType'],
                'occur_time' => $watchLog['updated_time'],
            );

            $this->getXapiService()->createStatement($statement);
            $this->getXapiService()->updateWatchLog($watchLog['id'], array('is_push' => 1));
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
