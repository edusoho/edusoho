<?php

namespace Biz\OpenCourse\Service;

use Biz\System\Annotation\Log;

interface OpenCourseService
{
    const LIVE_OPEN_TYPE = 'liveOpen';
    const OPEN_TYPE = 'open';

    /**
     * open_course.
     */
    public function getCourse($id);

    public function findCoursesByIds(array $ids);

    public function searchCourses($conditions, $orderBy, $start, $limit);

    /**
     * @param $conditions
     *
     * @return mixed
     * @before  searchCourseCount
     */
    public function countCourses($conditions);

    /**
     * @param $course
     *
     * @return mixed
     * @Log(level="info",module="open_course",action="create_course",message="创建公开课",targetType="open_course",param="result")
     */
    public function createCourse($course);

    public function updateCourse($id, $fields);

    public function deleteCourse($id);

    public function waveCourse($id, $field, $diff);

    public function favoriteCourse($courseId);

    public function unFavoriteCourse($courseId);

    public function changeCoursePicture($courseId, $data);

    public function getFavoriteByUserIdAndCourseId($userId, $courseId, $type);

    public function publishCourse($id);

    public function closeCourse($id);

    public function tryManageOpenCourse($courseId);

    public function findCourseTeachers($courseId);

    /**
     * open_course_lesson.
     */
    public function getLesson($id);

    public function getCourseLesson($courseId, $lessonId);

    public function findLessonsByIds(array $ids);

    public function findLessonsByCourseId($courseId);

    public function searchLessons($condition, $orderBy, $start, $limit);

    /**
     * @param $conditions
     *
     * @return mixed
     * @before  searchLessonCount
     */
    public function countLessons($conditions);

    /**
     * @param $lesson
     *
     * @return mixed
     * @Log(level="info",module="open_course",action="add_lesson",message="添加公开课时",targetType="open_course",param="result")
     */
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

    public function findFinishedLivesWithinTwoHours();

    public function updateLiveStatus($id, $status);

    /**
     * open_course_member.
     */
    public function getMember($id);

    public function getCourseMember($courseId, $userId);

    public function getCourseMemberByIp($courseId, $ip);

    public function getCourseMemberByMobile($courseId, $mobile);

    public function findMembersByCourseIds($courseIds);

    /**
     * @param $conditions
     *
     * @return mixed
     * @before  searchMemberCount
     */
    public function countMembers($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function createMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function setCourseTeachers($courseId, $teachers);

    public function getTodayOpenLiveCourseNumber();

    public function findOpenLiveCourse($conditions, $userId);
}
