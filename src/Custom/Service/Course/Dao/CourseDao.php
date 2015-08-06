<?php

namespace Custom\Service\Course\Dao;

interface CourseDao
{

    public function getPeriodicCoursesCount($rootId);

	public function findOtherPeriods($course);

}