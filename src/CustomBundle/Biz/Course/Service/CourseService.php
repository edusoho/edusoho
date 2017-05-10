<?php

namespace CustomBundle\Biz\Course\Service;

interface  CourseService extends  \Biz\Course\Service\CourseService
{
    public function getCustomCourseById($id);
}