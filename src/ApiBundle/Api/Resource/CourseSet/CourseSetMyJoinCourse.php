<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Annotation\ApiConf;
use AppBundle\Common\ArrayToolkit;

class CourseSetMyJoinCourse extends Resource
{
    public function search(ApiRequest $request, $courseSetId)
    {
        $conditions['courseSet'] = $courseSetId;
        $conditions['userId'] = $this->getCurrentUser()->getId();

        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $courseIds = array_column($members, 'courseId');

        $courses = $this->service('Course:CourseService')->findCoursesByIds($courseIds);

        return $courses;
    }
}