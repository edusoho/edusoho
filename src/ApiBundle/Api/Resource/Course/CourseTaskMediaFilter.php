<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskMediaFilter extends Filter
{
    protected $publicFields = array(
        'mediaType', 'media',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['mediaType']) && 'text' == $data['mediaType']) {
            $data['media']['content'] = $this->convertAbsoluteUrl($data['media']['content']);
        }
    }
}
