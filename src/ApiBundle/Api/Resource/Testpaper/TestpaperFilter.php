<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class TestpaperFilter extends Filter
{
    protected $publicFields = array('testpaper', 'items', 'task');

    protected function publicFields(&$data)
    {
        if (!empty($data['task'])) {
            $tasKFilter = new CourseTaskFilter();
            $tasKFilter->filter($data['task']);
        }
    }
}
