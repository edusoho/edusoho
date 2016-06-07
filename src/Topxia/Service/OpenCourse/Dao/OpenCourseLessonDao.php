<?php

namespace Topxia\Service\OpenCourse\Dao;

interface OpenCourseLessonDao
{
    public function getLesson($id);

    public function findLessonsByIds(array $ids);

    public function findLessonsByCourseId($courseId);

    public function searchLessons($condition, $orderBy, $start, $limit);

    public function searchLessonCount($conditions);

    public function addLesson($lesson);

    public function updateLesson($id, $fields);

    public function waveCourseLesson($id, $field, $diff);

    public function deleteLesson($id);

    public function deleteLessonsByCourseId($id);

    public function findTimeSlotOccupiedLessonsByCourseId($courseId, $startTime, $endTime, $excludeLessonId);
}
