<?php

namespace Topxia\Service\Course\Dao;

interface CourseLessonReplayDao
{
    public function addCourseLessonReplay($courseLessonReplay);

    public function getCourseLessonReplay($id);

    public function deleteLessonReplayByLessonId($lessonId);

    public function getCourseLessonReplayByLessonId($lessonId);

    public function deleteLessonReplayByCourseId($courseId);

    public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId);

    public function searchCourseLessonReplayCount($conditions);

    public function searchCourseLessonReplays($conditions, $orderBy, $start, $limit);

    public function deleteCourseLessonReplay($id);

    public function updateCourseLessonReplay($id, $fields);

    public function updateCourseLessonReplayByLessonId($lessonId, $fields);

}
