<?php

namespace Biz\Course\Service;

interface CourseService
{
    public function getCourse($id);

    public function findCoursesByIds($ids);

    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function createCourse($course);

    public function createChapter($chapter);

    public function updateChapter($courseId, $chapterId, $fields);

    public function updateCourse($id, $fields);

    public function updateCourseMarketing($id, $fields);

    public function updateCourseStatistics($id, $fields);

    public function deleteCourse($id);

    public function closeCourse($id);

    public function publishCourse($id, $userId);

    public function findCourseItems($courseId);

    public function tryManageCourse($courseId);

    public function getNextNumberAndParentId($courseId);

    public function tryTakeCourse($courseId);

    public function findStudentsByCourseId($courseId);

    public function countStudentsByCourseId($courseId);

    public function isCourseTeacher($courseId, $userId);

    public function isCourseStudent($courseId, $userId);

    public function createCourseStudent($courseId, $fields);

    public function removeCourseStudent($courseId, $userId);

    public function setMemberNoteNumber($courseId, $userId, $num);

    public function countLeaningCourseByUserId($userId, $filters = array());

    public function findLearningCourseByUserId($userId, $start, $limit, $filters = array());

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

    public function countMembers($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchCourses($conditions, $sort, $start, $limit);

    public function searchCourseCount($conditions);

}
