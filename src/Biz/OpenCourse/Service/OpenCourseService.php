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
     * @Log(module="open_course",action="create_course")
     */
    public function createCourse($course);

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     * @Log(module="open_course",action="update_course",param="id")
     */
    public function updateCourse($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="open_course",action="delete_course")
     */
    public function deleteCourse($id);

    public function waveCourse($id, $field, $diff);

    /**
     * @param $courseId
     * @param $data
     *
     * @return mixed
     * @Log(module="open_course",action="update_picture",funcName="getCourse",param="courseId")
     */
    public function changeCoursePicture($courseId, $data);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="open_course",action="pulish_course",funcName="getCourse")
     */
    public function publishCourse($id);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="open_course",action="close_course",funcName="getCourse")
     */
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
     * @Log(module="open_course",action="add_lesson")
     */
    public function createLesson($lesson);

    /**
     * @param $courseId
     * @param $lessonId
     * @param $fields
     *
     * @return mixed
     * @Log(module="open_course",action="update_lesson",funcName="getCourseLesson",param="courseId,lessonId")
     */
    public function updateLesson($courseId, $lessonId, $fields);

    public function waveCourseLesson($id, $field, $diff);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="open_course",action="delete_lesson")
     */
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

    /**
     * @param $courseId
     * @param $teachers
     *
     * @return mixed
     * @Log(module="open_course",action="update_teacher",funcName="getCourse",param="courseId")
     */
    public function setCourseTeachers($courseId, $teachers);

    public function getTodayOpenLiveCourseNumber();

    public function findOpenLiveCourse($conditions, $userId);

    public function countLiveCourses($conditions = []);

    public function searchAndSortLiveCourses($conditions = [], $start, $limit);
}
