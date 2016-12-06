<?php

namespace Biz\LiveActivity;

use Biz\Activity\Config\Activity;

class LiveActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '直播',
            'icon' => 'es-icon es-icon-videocam'
        );
    }

    protected function registerListeners()
    {
        return array(
            'live.finish' => 'Biz\\LiveActivity\\Listener\\LiveFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:LiveActivity:create',
            'edit'   => 'WebBundle:LiveActivity:edit',
            'show'   => 'WebBundle:LiveActivity:show'
        );
    }

    public function create($fields)
    {
        return $this->getLiveActivityService()->createLiveActivity($fields);
    }

    public function update($id, $fields)
    {
        return $this->getLiveActivityService()->updateLiveActivity($id, $fields);
    }

    public function get($targetId)
    {
        return $this->getLiveActivityService()->getLiveActivity($targetId);
    }

    public function delete($targetId)
    {
        return $this->getLiveActivityService()->deleteLiveActivity($targetId);
    }

    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('LiveActivity:LiveActivityService');
    }
}
