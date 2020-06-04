<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Service\LiveActivityService;

class Live extends Activity
{
    public function sync($activity, $config = [])
    {
        return [];
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        return [];
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }
}
