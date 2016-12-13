<?php

namespace Biz\Activity\Type;


use Biz\Activity\Config\Activity;
use Topxia\Common\ArrayToolkit;

class Text extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTextActivityDao()->get($targetId);
    }

    public function update($targetId, $fields)
    {
        $text = ArrayToolkit::parts($fields, array(
            'finishType',
            'finishDetail'
        ));

        $biz                   = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];
        return $this->getTextActivityDao()->update($targetId, $text);
    }

    public function isFinished($activityId)
    {
        $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
        $activity = $this->getActivityService()->getActivity($activityId);
        $textActivity = $this->getTextActivityDao()->get($activity['mediaId']);
        return !empty($result) 
                && $textActivity['finishType'] == 'time' 
                && $result > $textActivity['finishDetail'];
    }

    public function delete($targetId)
    {
        return $this->getTextActivityDao()->delete($targetId);
    }

    public function create($fields)
    {
        $text                  = ArrayToolkit::parts($fields, array(
            'finishType',
            'finishDetail'
        ));
        $biz                   = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];
        return $this->getTextActivityDao()->create($text);
    }

    protected function getTextActivityDao()
    {
        return $this->getBiz()->dao('Activity:TextActivityDao');
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

}