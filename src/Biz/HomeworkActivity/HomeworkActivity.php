<?php

namespace Biz\HomeworkActivity;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

class HomeworkActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '做作业题',
            'icon' => 'es-icon es-icon-exam'
        );
    }

    protected function registerListeners()
    {
        return array(
            'homework.finish' => 'Biz\\HomeworkActivity\\Listener\\HomeworkFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:HomeworkActivity:create',
            'edit'   => 'WebBundle:HomeworkActivity:edit',
            'show'   => 'WebBundle:HomeworkActivity:show'
        );
    }

    public function get($targetId)
    {
        return $this->getTestpaperActivityService()->getActivity($targetId);
    }

    public function create($fields)
    {
        /*$fields = $this->filterFields($fields);

    return $this->getTestpaperActivityService()->createActivity($fields);*/
    }

    public function update($targetId, $fields)
    {
        /*$activity = $this->get($targetId);

    if (!$activity) {
    throw new ResourceNotFoundException('testpaperActivity', $targetId);
    }

    $fields = $this->filterFields($fields);

    return $this->getTestpaperActivityService()->updateActivity($activity['id'], $fields);*/
    }

    public function delete($targetId)
    {
        //return $this->getTestpaperActivityService()->deleteActivity($targetId);
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
            'finisheScore',
            'requireCredit',
            'testMode'
        ));

        $finishCondition = array();

        if (!empty($fields['finishCondition'])) {
            $finishCondition['type'] = $fields['finishCondition'];
        }

        if (!empty($fields['finisheScore'])) {
            $finishCondition['finisheScore'] = $fields['finisheScore'];
            unset($fields['finisheScore']);
        }

        $fields['finishCondition'] = $finishCondition;

        return $fields;
    }

    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('TestpaperActivity:TestpaperActivityService');
    }
}
