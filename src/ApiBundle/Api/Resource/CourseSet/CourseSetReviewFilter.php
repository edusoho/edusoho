<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;

class CourseSetReviewFilter extends Filter
{
    protected $publicFields = array(
        'id', 'title', 'content', 'rating', 'private', 'createdTime', 'parentId',
        'updatedTime', 'courseSetId', 'user', 'course'
    );

    protected function customFilter(&$data)
    {
        //评价的用户
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);

        $data['course'] = ArrayToolkit::parts($data['course'], array('title', 'id'));
    }
}