<?php

namespace Biz\Course\Service;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

interface CourseService
{
    public function getCourse($id);

    public function findCoursesByIds($ids);

    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function getFirstPublishedCourseByCourseSetId($courseSetId);

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

    public function tryManageCourse($courseId, $courseSetId = 0);

    public function getNextNumberAndParentId($courseId);

    public function tryTakeCourse($courseId);

    public function canTakeCourse($course);

    public function findStudentsByCourseId($courseId);

    public function countStudentsByCourseId($courseId);

    public function hasCourseManagerRole($courseId = 0);

    public function getUserRoleInCourse($courseId, $userId);


    /**
     * @param integer $userId
     *
     * @return array[]
     */
    public function findTeachingCoursesByUserId($userId);

    /**
     * @param integer $userId
     *
     * @return array[]
     */
    public function findLearnCoursesByUserId($userId);

    /**
     * @param array $ids
     *
     * @return array[]
     */
    public function findPublicCoursesByIds(array $ids);

    /**
     * @before becomeStudent
     * @param  $courseId
     * @param  $fields
     * @return mixed
     */
    public function createCourseStudent($courseId, $fields);

    /**
     * @before removeStudent
     * @param  $courseId
     * @param  $userId
     * @return mixed
     */
    public function removeCourseStudent($courseId, $userId);

    /**
     * collect course
     *
     * @param $id
     * @throws AccessDeniedException
     * @return bool
     */
    public function favorite($id);

    /**
     * cancel collected course
     * @param $id
     *
     * @throws AccessDeniedException
     * @return bool
     */
    public function unfavorite($id);

    /**
     * @param integer $userId
     * @param integer $courseId
     *
     * @return bool
     */
    public function isUserFavorite($userId, $courseId);

    /**
     * @param int $userId
     *
     * @return integer
     */
    public function countUserFavorites($userId);

    /**
     * @param int $userId
     *
     * @param int    $start
     * @param int    $limit
     *
     * @return array[]
     */
    public function searchUserFavorites($userId, $start, $limit);

    //---start 前两个已经重构了，后面的四个也需要重构，目前还没有用到，用到的时候在重构
    public function findUserLeaningCourseCount($userId, $filters = array());

    public function findUserLeaningCourses($userId, $start, $limit, $filters = array());

    public function findUserLeanedCourseCount($userId, $filters = array());

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

    // public function findUserLearnCourses($userId, $start, $limit);

    //public function findUserLearnCourseCount($userId);

    //---end
    public function searchCourses($conditions, $sort, $start, $limit);

    public function searchCourseCount($conditions);

    public function sortCourseItems($courseId, $ids);
}
