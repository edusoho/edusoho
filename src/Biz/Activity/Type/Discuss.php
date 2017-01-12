<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityLearnLogService;

class Discuss extends Activity
{
    protected function registerListeners()
    {
        return array(
        );
    }

    public function isFinished($activityId)
    {
        $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'discuss.finish');
        return !empty($result);
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }
}
