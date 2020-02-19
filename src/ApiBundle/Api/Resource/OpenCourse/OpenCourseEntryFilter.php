<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Resource\Filter;

class OpenCourseEntryFilter extends Filter
{
    protected $publicFields = array('lesson', 'live', 'replay', 'message');

    protected function publicFields(&$data)
    {
        if (!empty($data['lesson'])) {
            $data['lesson']['startTime'] = date('c', $data['lesson']['startTime']);
            $data['lesson']['endTime'] = date('c', $data['lesson']['endTime']);
        }
    }
}
