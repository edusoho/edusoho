<?php

namespace Biz\Activity\Type;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

class Flash extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => 'Flash',
            'icon' => 'es-icon es-icon-flashclass'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'AppBundle:Flash:create',
            'edit'   => 'AppBundle:Flash:edit',
            'show'   => 'AppBundle:Flash:show'
        );
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $flash    = $this->getFlashActivityDao()->get($activity['mediaId']);
        if ($flash['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return $result > $flash['finishDetail'];
        }

        if ($flash['finishType'] == 'click') {
            $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'flash.finish');
            return !empty($result);
        }
        return false;
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        $flash = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                    = $this->getBiz();
        $flash['createdUserId'] = $biz['user']['id'];

        $flash = $this->getFlashActivityDao()->create($flash);
        return $flash;
    }

    public function update($targetId, &$fields, $activity)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        return $this->getFlashActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getFlashActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getFlashActivityDao()->get($targetId);
    }

    protected function getFlashActivityDao()
    {
        return $this->getBiz()->dao('Activity:FlashActivityDao');
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }
}
