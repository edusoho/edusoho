<?php

namespace Biz\S2B2C\Service;

interface CourseProductService
{
    public function syncCourses($localCourseSet, $product);

    public function syncCourseMain($courseId);
}
