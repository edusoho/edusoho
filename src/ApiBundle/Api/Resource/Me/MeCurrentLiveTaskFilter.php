<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class MeCurrentLiveTaskFilter extends Filter
{
    public function filter(&$data)
    {
        $courseFilter = new CourseTaskFilter();
        $courseFilter->filter($data);
    }
}
