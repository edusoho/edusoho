<?php

namespace Topxia\Service\Course\Dao;

interface CourseLessonReplayDao
{
    public function addCourseLessonReplay($courseLessonReplay);

    public function getCourseLessonReplay($id);

    public function deleteLessonReplayByLessonId($lessonId, $lessonType);

    public function getCourseLessonReplayByLessonId($lessonId, $lessonType);

    public function deleteLessonReplayByCourseId($courseId, $lessonType);

    public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId, $lessonType);

    public function searchCourseLessonReplayCount($conditions);

    public function searchCourseLessonReplays($conditions, $orderBy, $start, $limit);

    public function deleteCourseLessonReplay($id);

    public function updateCourseLessonReplay($id, $fields);

    public function updateCourseLessonReplayByLessonId($lessonId, $fields, $lessonType);

}
