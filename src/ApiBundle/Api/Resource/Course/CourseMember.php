<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseMember extends Resource
{
    public function add(Request $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        if ($course['price'] > 0) {
            throw new InvalidArgumentException('不是免费课程,不能直接加入');
        }

        $member = $this->service('Course:MemberService')->becomeStudent($course['id'], $this->getCurrentUser()->id);

        if ($member) {
            return array('success' => true);
        }

        return array('success' => false);
    }

}