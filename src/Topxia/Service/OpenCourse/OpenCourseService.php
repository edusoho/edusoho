<?php

namespace Topxia\Service\OpenCourse;

interface OpenCourseService
{
    /**
     * open_course
     */
    public function getCourse($id);

    public function findCoursesByIds(array $ids);

    public function searchCourses($conditions, $orderBy, $start, $limit);

    public function searchCourseCount($conditions);

    public function createCourse($course);

    public function updateCourse($id, $fields);

    public function deleteCourse($id);

    public function waveCourse($id, $field, $diff);

    public function favoriteCourse($courseId);

    public function unFavoriteCourse($courseId);

    public function getFavoriteByUserIdAndCourseId($userId, $courseId, $type);

    public function publishCourse($id);

    public function closeCourse($id);

    public function tryManageOpenCourse($courseId);

    public function findCourseTeachers($courseId);

    /**
     * open_course_lesson
     */
    public function getLesson($id);

    public function getCourseLesson($courseId, $lessonId);

    public function findLessonsByIds(array $ids);

    public function findLessonsByCourseId($courseId);

    public function searchLessons($condition, $orderBy, $start, $limit);

    public function searchLessonCount($conditions);

    public function createLesson($lesson);

    public function updateLesson($courseId, $lessonId, $fields);

    public function waveCourseLesson($id, $field, $diff);

    public function deleteLesson($id);

    public function getLessonItems($courseId);

    public function unpublishLesson($courseId, $lessonId);

    public function publishLesson($courseId, $lessonId);

    public function resetLessonMediaId($lessonId);

    public function sortCourseItems($courseId, array $items);

    public function liveLessonTimeCheck($courseId, $lessonId, $startTime, $length);

    public function getNextLesson($courseId, $lessonId);

    public function generateLessonVideoReplay($courseId, $lessonId, $fileId);

    /**
     * open_course_member
     */
    public function getMember($id);

    public function getCourseMember($courseId, $userId);

    public function getCourseMemberByIp($courseId, $ip);

    public function getCourseMemberByMobile($courseId, $mobile);

    public function findMembersByCourseIds($courseIds);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function createMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function setCourseTeachers($courseId, $teachers);

}
