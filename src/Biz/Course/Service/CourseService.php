<?php

namespace Biz\Course\Service;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

interface CourseService
{
    const NORMAL__COURSE_TYPE = 'normal';
    const DEFAULT_COURSE_TYPE = 'default';

    const FREE_LEARN_MODE = 'freeMode';
    const LOCK_LEARN_MODE = 'lockMode';

    public function getCourse($id);

    public function hasCourseManagerRole($courseId = 0);

    public function findCoursesByIds($ids);

    public function findCoursesByCourseSetIds(array $setIds);

    public function findCoursesByParentIdAndLocked($parentId, $locked);

    public function findPublishedCoursesByCourseSetId($courseSetId);

    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function getDefaultCoursesByCourseSetIds($courseSetIds);

    public function getFirstPublishedCourseByCourseSetId($courseSetId);

    public function getFirstCourseByCourseSetId($courseSetId);

    public function createCourse($course);

    /**
     * 复制教学计划.
     *
     * @param array $newCourse
     *
     * @return mixed
     */
    public function copyCourse($newCourse);

    public function getChapter($courseId, $chapterId);

    public function createChapter($chapter);

    public function updateChapter($courseId, $chapterId, $fields);

    public function deleteChapter($courseId, $chapterId);

    public function findChaptersByCourseId($courseId);

    public function updateCourse($id, $fields);

    public function updateCourseMarketing($id, $fields);

    public function validateCourseRewardPoint($fields);

    public function updateCourseStatistics($id, $fields);

    public function deleteCourse($id);

    public function closeCourse($id);

    public function publishCourse($id, $withTasks = false);

    /**
     * @param  $courseId
     * @param int $limitNum 限制取几条任务，默认不限制
     *
     * @return mixed
     */
    public function findCourseItems($courseId, $limitNum = 0);

    /**
     * @param $courseId
     * @param array $paging array('direction' => 'up or down', 'offsetSeq' => '0', 'limit' => 10)
     *
     * @return mixed
     */
    public function findCourseItemsByPaging($courseId, $paging = array());

    public function tryManageCourse($courseId, $courseSetId = 0);

    public function tryTakeCourse($courseId);

    public function canTakeCourse($course);

    public function canJoinCourse($id);

    public function canLearnCourse($id);

    public function canLearnTask($taskId);

    public function findStudentsByCourseId($courseId);

    public function findTeachersByCourseId($courseId);

    public function countStudentsByCourseId($courseId);

    public function getUserRoleInCourse($courseId, $userId);

    public function findPriceIntervalByCourseSetIds($courseSetIds);

    /**
     * 获取用户在教的教学计划.
     *
     * @param int  $courseSetId
     * @param bool $onlyPublished
     *
     * @throws AccessDeniedException
     *
     * @return mixed
     */
    public function findUserTeachingCoursesByCourseSetId($courseSetId, $onlyPublished = true);

    /**
     * @param int  $userId
     * @param bool $onlyPublished 是否只需要发布后的教学计划
     *
     * @return array[]
     */
    public function findTeachingCoursesByUserId($userId, $onlyPublished = true);

    /**
     * @param int $userId
     *
     * @return array[]
     */
    public function findLearnCoursesByUserId($userId);

    public function findUserTeachCourseCount($conditions, $onlyPublished = true);

    public function findUserTeachCourses($conditions, $start, $limit, $onlyPublished = true);

    public function findUserLearnCourseIds($userId);

    public function countUserLearnCourses($userId);

    /**
     * @param array $ids
     *
     * @return array[]
     */
    public function findPublicCoursesByIds(array $ids);

    public function countUserLearningCourses($userId, $filters = array());

    /**
     * filter 支持 type classroomId locked ...
     *
     * @param  $userId
     * @param  $start
     * @param  $limit
     * @param array $filters
     *
     * @return mixed
     */
    public function findUserLearningCourses($userId, $start, $limit, $filters = array());

    public function countUserLearnedCourses($userId, $filters = array());

    public function findUserLearnedCourses($userId, $start, $limit, $filters = array());

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

    public function searchCourses($conditions, $sort, $start, $limit, $columns = array());

    public function searchCourseCount($conditions);

    public function sortCourseItems($courseId, $ids);

    public function analysisCourseDataByTime($startTime, $endTime);

    public function countCourses(array $conditions);

    public function countCoursesGroupByCourseSetIds($courseSetIds);

    public function getMinAndMaxPublishedCoursePriceByCourseSetId($CourseSetId);

    public function updateMaxRateByCourseSetId($courseSetId, $maxRate);

    public function recommendCourseByCourseSetId($courseSetId, $fields);

    public function cancelRecommendCourseByCourseSetId($courseSetId);

    public function findUserLearningCourseCountNotInClassroom($userId, $filters = array());

    public function findUserLearningCoursesNotInClassroom($userId, $start, $limit, $filters = array());

    public function findUserLeanedCourseCount($userId, $filters = array());

    public function findUserLearnedCoursesNotInClassroom($userId, $start, $limit, $filters = array());

    public function findUserLearnCourseCountNotInClassroom($userId, $onlyPublished = true, $filterReservation = false);

    public function findUserLearnCoursesNotInClassroom($userId, $start, $limit, $onlyPublished = true, $filterReservation = false);

    public function findUserLearnCoursesNotInClassroomWithType($userId, $type, $start, $limit, $onlyPublished = true);

    public function findUserTeachCourseCountNotInClassroom($conditions, $onlyPublished = true);

    public function findUserTeachCoursesNotInClassroom($conditions, $start, $limit, $onlyPublished = true);

    public function findUserFavoritedCourseCountNotInClassroom($userId);

    public function findUserFavoritedCoursesNotInClassroom($userId, $start, $limit);

    public function findCourseTasksAndChapters($courseId);

    public function updateCategoryByCourseSetId($courseSetId, $categoryId);

    public function calculateLearnProgressByUserIdAndCourseIds($userId, array $courseIds);

    public function convertTasks($tasks, $course);

    public function findUserManageCoursesByCourseSetId($userId, $courseSetId);

    public function unlockCourse($courseId);

    public function getFavoritedCourseByUserIdAndCourseSetId($userId, $courseSetId);

    public function buildCourseExpiryDataFromClassroom($expiryMode, $expiryValue);

    public function hitCourse($courseId);

    /**
     * 重新统计用户的学习数据
     *
     * @param $courseId
     * @param $userId
     *
     * @return mixed
     */
    public function recountLearningData($courseId, $userId);

    public function tryFreeJoin($courseId);

    public function findLiveCourse($conditions, $userId, $role);

    public function changeShowPublishLesson($courseId, $status);

    public function countCoursesByCourseSetId($courseSetId);

    //排序教学计划
    public function sortCourse($courseSetId, $ids);

    public function sortByCourses($courses);

    public function countCourseItems($course);

    /**
     * 如果 约排课已开启，不额外添加查询条件，
     * 如果 未开启，添加 excludeTypes = 'reservation' 的查询条件
     *    （如果已经存在 excludeTypes 属性，则额外新增，非替换）
     */
    public function appendReservationConditions($conditions);

    //修改课程基础信息
    public function updateBaseInfo($id, $fields);

    /**
     * 是否能修改基础信息
     * 管理员可以修改
     * 课程老师，后台设置可修改营销设置可修改
     */
    public function canUpdateCourseBaseInfo($courseId);
}
