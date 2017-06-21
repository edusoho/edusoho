<?php

namespace CustomBundle\Biz\Course\Service\Impl;


use CustomBundle\Biz\Course\Service\CourseService;

class CourseServiceImpl  extends \Biz\Course\Service\Impl\CourseServiceImpl implements  CourseService
{
    public function getCustomCourseById($id)
    {
        return array(2);
        // TODO: Implement getCustomCourseById() method.
    }

}