<?php

namespace Biz\Activity\Type;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

class Testpaper extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperActivityService()->getActivity($targetId);
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    public function update($targetId, $fields)
    {
        $activity = $this->get($targetId);

        if (!$activity) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $fields = $this->filterFields($fields);

        return $this->getTestpaperActivityService()->updateActivity($activity['id'], $fields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperActivityService()->deleteActivity($targetId);
    }

    protected function getListeners()
    {
        return array();
    }

    protected function filterFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'doTimes',
            'redoInterval',
            'limitedTime',
            'checkType',
            'finishCondition',
            'finishScore',
            'requireCredit',
            'testMode'
        ));

        $finishCondition = array();

        if (!empty($fields['finishCondition'])) {
            $finishCondition['type'] = $fields['finishCondition'];
        }

        if (!empty($fields['finishScore'])) {
            $finishCondition['finishScore'] = $fields['finishScore'];
            unset($fields['finishScore']);
        }

        $fields['finishCondition'] = $finishCondition;

        return $fields;
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }
}
