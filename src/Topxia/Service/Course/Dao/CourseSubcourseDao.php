<?php

namespace Topxia\Service\Course\Dao;

interface CourseSubcourseDao
{
	public function get($id);

    public function findSubcoursesByCourseId($courseId);

    public function findSubcoursesCountByCourseId($courseId);

    public function addSubcourse($fields);

    public function delete($id);

    public function update($id, $fields);
}