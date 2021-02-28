<?php

namespace Biz\Course\Service;

interface CourseDeleteService
{
    /**
     * 删除课程及相关信息.
     *
     * @param $courseSetId
     */
    public function deleteCourseSet($courseSetId);

    /**
     * 删除教学计划及相关信息.
     *
     * @param  $courseId
     *
     * @return mixed
     */
    public function deleteCourse($courseId);

    public function deleteCourseMaterial($courseId);

    public function deleteCourseChapter($courseId);

    public function deleteTaskResult($courseId);

    public function deleteCourseMember($courseId);

    public function deleteCourseNote($courseId);

    public function deleteCourseThread($courseId);

    public function deleteCourseReview($courseId);

    public function deleteCourseFavorite($courseId);

    public function deleteCourseAnnouncement($courseId);

    public function deleteCourseStatus($courseId);

    public function deleteCourseCoversation($courseId);
}
