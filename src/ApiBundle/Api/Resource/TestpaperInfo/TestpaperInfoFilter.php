<?php

namespace ApiBundle\Api\Resource\TestpaperInfo;

use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class TestpaperInfoFilter extends Filter
{
    protected $publicFields = array('testpaper', 'items', 'task', 'testpaperResult');

    protected function publicFields(&$data)
    {
        if (!empty($data['task'])) {
            $tasKFilter = new CourseTaskFilter();
            $tasKFilter->filter($data['task']);
        }
    }
}
