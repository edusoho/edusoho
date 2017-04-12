<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;

class Course extends Resource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        $this->getOCUtil()->single($course, array('creator', 'teacherIds'));

        return $course;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->service('Course:CourseService')->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->service('Course:CourseService')->searchCourseCount($conditions);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    public function add()
    {

    }

}