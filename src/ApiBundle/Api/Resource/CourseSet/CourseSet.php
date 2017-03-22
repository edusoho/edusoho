<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseSet extends Resource
{
    public function get(Request $request, $courseSetId)
    {
        $currentUser = $this->getCurrentUser();
        $conditions = array('id' => $courseSetId);
        if (!$currentUser->isAdmin()) {
            $conditions['status'] = 'published';
        }

        $results = $this->createService('Course:CourseSet')->searchCourseSets($courseSetId, array(), 0, 1);

        if (empty($results)) {
            throw new ResourceNotFoundException('课程不存在或者无权限访问', 9);
        }

        return $results[0];
    }
}