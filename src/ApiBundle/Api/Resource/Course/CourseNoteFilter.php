<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseNoteFilter extends Filter
{
    protected $publicFields = [];

    public function publicFields(&$data)
    {
        $data['course'] = $data['target'];
        unset($data['target']);
    }
}
