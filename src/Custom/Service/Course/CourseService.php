<?php

namespace Custom\Service\Course;

// this is a sample

interface CourseService
{
    public function updateCourse($id, $fields);

	public function findOtherPeriods($courseId);

}