<?php

namespace Topxia\Service\OpenCourse\Dao;

interface OpenCourseDao
{
    public function getCourse($id);

    public function findCoursesByIds(array $ids);

    public function searchCourses($conditions, $orderBy, $start, $limit);

    public function searchCourseCount($conditions);

    public function addCourse($course);

    public function updateCourse($id, $fields);

    public function deleteCourse($id);

    public function waveCourse($id, $field, $diff);

}
