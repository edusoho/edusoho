<?php

namespace Biz\LiveActivity\Service\Impl;

use Biz\BaseService;
use Biz\LiveActivity\Service\LiveActivityService;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    public function getActivityDetail($id)
    {
        $this->getActivityDao()->get($id);
    }

    public function createActivityDetail($activity)
    {
        $data = array(
            'fromCourseId'    => $activity['fromCourseId'],
            'fromCourseSetId' => $activity['fromCourseSetId'],
            'time_last'       => $activity['time_last']
        );
        return $this->getActivityDao()->create($data);
    }

    public function updateActivityDetail($id, $fields)
    {
        $data = array(
            'fromCourseId'    => $fields['fromCourseId'],
            'fromCourseSetId' => $fields['fromCourseSetId'],
            'time_last'       => $fields['time_last']
        );
        return $this->getActivityDao()->update($id, $data);
    }

    public function deleteActivityDetail($id)
    {
        return $this->getActivityDao()->delete($id);
    }

    protected function getActivityDao()
    {
        return $this->createDao('LiveActivity:LiveActivityDao');
    }
}
