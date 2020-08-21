<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;

class ClassroomReviewFilter extends Filter
{
    protected $publicFields = [
        'id', 'user', 'classroomId', 'target', 'content', 'rating', 'parentId', 'posts', 'updatedTime', 'createdTime',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['target']['id'])) {
            $data['classroomId'] = $data['target']['id'];
            unset($data['target']);
        }
    }
}
