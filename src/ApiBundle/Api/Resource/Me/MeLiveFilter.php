<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;

class MeLiveFilter extends Filter
{
    protected $publicFields = array(
        'id', 'seq', 'categoryId', 'title', 'isFree', 'isOptional', 'startTime', 'endTime', 'mode', 'status',
        'number', 'type', 'mediaSource', 'length', 'activity', 'course', 'classroom',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['startTime'])) {
            $data['startTime'] = date('c', (int) $data['startTime']);
        }

        if (isset($data['endTime'])) {
            $data['endTime'] = date('c', (int) $data['endTime']);
        }

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::SIMPLE_MODE);
        $courseFilter->filter($data['course']);

        $activityFilter = new ActivityFilter();
        $activityFilter->setMode(Filter::SIMPLE_MODE);
        $activityFilter->filter($data['activity']);
    }
}
