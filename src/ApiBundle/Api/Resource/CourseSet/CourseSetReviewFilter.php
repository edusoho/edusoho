<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Course\CourseReviewFilter;
use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;

class CourseSetReviewFilter extends Filter
{
    protected function customFilter(&$data)
    {
        //评价的用户
        $courseReviewFilter = new CourseReviewFilter();
        $courseReviewFilter->filter($data);
    }
}