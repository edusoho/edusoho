<?php

namespace Biz\Course\Service;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

interface CourseService
{
    public function getCourse($id);

    public function findCoursesByIds($ids);

    public function findCoursesByCourseSetIds(array $setIds);

    public function findCoursesByParentIdAndLocked($parentId, $locked);

    public function findPublishedCoursesByCourseSetId($courseSetId);

    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function getDefaultCoursesByCourseSetIds($courseSetIds);

    public function getFirstPublishedCourseByCourseSetId($courseSetId);

    public function createCourse($course);

    /**
     * 复制教学计划
     * @param  array   $fields
     * @return mixed
     */
    public function copyCourse($fields);

    public function createChapter($chapter);

    public function updateChapter($courseId, $chapterId, $fields);

    public function updateCourse($id, $fields);

    public function updateCourseMarketing($id, $fields);

    public function updateCourseStatistics($id, $fields);

    public function deleteCourse($id);

    public function closeCourse($id);

    public function publishCourse($id);

    public function findCourseItems($courseId);

    public function tryManageCourse($courseId, $courseSetId = 0);

    public function getNextNumberAndParentId($courseId);

    public function tryTakeCourse($courseId);

    public function canTakeCourse($course);

    public function findStudentsByCourseId($courseId);

    public function findTeachersByCourseId($courseId);

    public function countStudentsByCourseId($courseId);

    public function getUserRoleInCourse($courseId, $userId);

    public function findPriceIntervalByCourseSetIds($courseSetIds);

    /**
     * 获取用户在教的教学计划
     *
     * @param  integer                 $courseSetId
     * @param  bool                    $onlyPublished
     * @throws AccessDeniedException
     * @return mixed
     */
    public function findUserTeachingCoursesByCourseSetId($courseSetId, $onlyPublished = true);

    /**
     * @param  integer   $userId
     * @param  bool      $onlyPublished 是否只需要发布后的教学计划
     * @return array[]
     */
    public function findTeachingCoursesByUserId($userId, $onlyPublished = true);

    /**
     * @param  integer   $userId
     * @return array[]
     */
    public function findLearnCoursesByUserId($userId);

    public function findUserTeachCourseCount($conditions, $onlyPublished = true);

    public function findUserTeachCourses($conditions, $start, $limit, $onlyPublished = true);

    /**
     * @param  array     $ids
     * @return array[]
     */
    public function findPublicCoursesByIds(array $ids);

    //---start 前两个已经重构了，后面的四个也需要重构，目前还没有用到，用到的时候在重构
    public function countUserLearningCourses($userId, $filters = array());

    public function findUserLearningCourses($userId, $start, $limit, $filters = array());

    public function countUserLearnedCourses($userId, $filters = array());

    public function findUserLearnedCourses($userId, $start, $limit, $filters = array());

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

    // public function findUserLearnCourses($userId, $start, $limit);

    //public function findUserLearnCourseCount($userId);

    //---end
    public function searchCourses($conditions, $sort, $start, $limit);

    public function searchCourseCount($conditions);

    public function sortCourseItems($courseId, $ids);

    public function deleteChapter($courseId, $chapterId);

    public function analysisCourseDataByTime($startTime, $endTime);

    public function countCourses(array $conditions);
}
