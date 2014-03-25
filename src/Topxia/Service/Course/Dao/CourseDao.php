<?php

namespace Topxia\Service\Course\Dao;

interface CourseDao
{
    const TABLENAME = 'course';

    public function getCourse($id);

    public function findCoursesByIds(array $ids);

    public function findCoursesByHaveMemberLevelIds($start,$limit);

    public function findCoursesByMemberLevelId($memberLevelId,$start,$limit);

    public function findCoursesByMemberLevelIdCount($memberLevelId);

	public function searchCourses($conditions, $orderBy, $start, $limit);

	public function searchCourseCount($conditions);

    public function addCourse($course);

    public function updateCourse($id, $fields);

    public function deleteCourse($id);

}