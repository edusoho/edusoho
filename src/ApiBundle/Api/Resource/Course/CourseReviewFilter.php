<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseReviewFilter extends Filter
{
    protected $publicFields = [
        'id', 'title', 'content', 'rating', 'private', 'createdTime', 'parentId',
        'updatedTime', 'courseSetId', 'user', 'course', 'target', 'posts',
    ];

    protected function publicFields(&$data)
    {
        $data['course'] = $data['target'];
        unset($data['target']);
    }
}
