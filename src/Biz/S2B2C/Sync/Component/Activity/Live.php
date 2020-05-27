<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Service\LiveActivityService;

class Live extends Activity
{
    public function sync($activity, $config = array())
    {
        return array();
    }

    public function updateToLastedVersion($activity, $config = array())
    {
        return array();
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }
}
