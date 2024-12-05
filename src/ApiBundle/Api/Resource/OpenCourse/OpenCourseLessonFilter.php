<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Resource\Filter;

class OpenCourseLessonFilter extends Filter
{
    protected $publicFields = ['id', 'type', 'title', 'status', 'seq', 'length', 'startTime', 'replayEnable', 'progressStatus', 'replayStatus', 'copyId', 'replayId', 'liveTitle'];

    protected function publicFields(&$data)
    {
        if (!empty($data)) {
            $data['editable'] = 'liveOpen' != $data['type'] || $data['startTime'] > time();
        }
    }
}
