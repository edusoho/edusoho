<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Service\LiveActivityService;
use Biz\Activity\Service\ActivityLearnLogService;

class Live extends Activity
{
    protected function registerListeners()
    {
        return array(
        );
    }

    public function create($fields)
    {
        return $this->getLiveActivityService()->createLiveActivity($fields);
    }

    public function copy($activity, $config = array())
    {
        $biz         = $this->getBiz();
        $live        = $this->getLiveActivityService()->getLiveActivity($activity['mediaId']);
        $refLiveroom = $config['refLiveroom'];
        if (!$refLiveroom) {
            $activity['fromUserId'] = $biz['user']['id'];
            $activity['_base_url']  = ''; //todo 临时赋值
            unset($activity['id']);
            return $this->getLiveActivityService()->createLiveActivity($activity);
        }

        return $live;
    }

    public function update($id, &$fields, $activity)
    {
        return $this->getLiveActivityService()->updateLiveActivity($id, $fields, $activity);
    }

    public function get($targetId)
    {
        return $this->getLiveActivityService()->getLiveActivity($targetId);
    }

    public function delete($targetId)
    {
        return $this->getLiveActivityService()->deleteLiveActivity($targetId);
    }

    public function isFinished($activityId)
    {
        $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'live.finish');
        return !empty($result);
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }
}
