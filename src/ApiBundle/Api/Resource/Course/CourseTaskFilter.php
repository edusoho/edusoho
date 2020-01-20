<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Filter;

class CourseTaskFilter extends Filter
{
    protected $publicFields = array(
        'activity', 'id', 'title', 'isFree', 'isOptional', 'startTime', 'endTime', 'status', 'length', 'mode', 'type', 'mediaSource', 'lock', 'number', 'seq', 'result', 'subtitlesUrls', 'categoryId',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['result'])) {
            $resultFilter = new CourseTaskResultFilter();
            $resultFilter->setMode(Filter::SIMPLE_MODE);
            $resultFilter->filter($data['result']);
        }

        if (!empty($data['activity'])) {
            $activityFilter = new ActivityFilter();
            $activityFilter->filter($data['activity']);
        }
    }
}
