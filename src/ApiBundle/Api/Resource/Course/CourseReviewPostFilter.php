<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseReviewPostFilter extends Filter
{
    protected $publicFields = array(
        'id', 'title', 'content', 'rating', 'private', 'createdTime', 'parentId',
        'updatedTime', 'courseSetId', 'user', 'course',
    );

    protected function publicFields(&$data)
    {
        //评价的用户
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::SIMPLE_MODE);
        $courseFilter->filter($data['course']);
    }
}
