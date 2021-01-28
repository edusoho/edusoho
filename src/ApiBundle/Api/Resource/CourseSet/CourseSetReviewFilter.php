<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Course\CourseReviewFilter;
use ApiBundle\Api\Resource\Filter;

class CourseSetReviewFilter extends Filter
{
    public function filter(&$data)
    {
        //评价的用户
        $courseReviewFilter = new CourseReviewFilter();
        $courseReviewFilter->filter($data);
    }
}
