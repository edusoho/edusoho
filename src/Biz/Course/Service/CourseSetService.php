<?php

namespace Biz\Course\Service;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

interface CourseSetService
{
    /**
     * collect course set
     *
     * @param  $id
     * @throws AccessDeniedException
     * @return bool
     */
    public function favorite($id);

    /**
     * cancel collected course set
     *
     * @param  $id
     * @throws AccessDeniedException
     * @return bool
     */
    public function unfavorite($id);

    /**
     * @param  int    $userId
     * @param  int    $courseSetId
     * @return bool
     */
    public function isUserFavorite($userId, $courseSetId);

    public function tryManageCourseSet($id);

    /**
     * @param  integer   $userId
     * @return integer
     */
    public function countUserLearnCourseSets($userId);

    /**
     * @param  integer   $userId
     * @param  integer   $start
     * @param  integer   $limit
     * @return array[]
     */
    public function searchUserLearnCourseSets($userId, $start, $limit);

    /**
     * @param  integer   $userId
     * @param  array     $conditions
     * @return integer
     */
    public function countUserTeachingCourseSets($userId, array $conditions);

    /**
     * @param  integer   $userId
     * @param  array     $conditions
     * @param  integer   $start
     * @param  integer   $limit
     * @return array[]
     */
    public function searchUserTeachingCourseSets($userId, array $conditions, $start, $limit);

    /**
     * @param  integer[] $courseIds
     * @return array[]
     */
    public function findCourseSetsByCourseIds(array $courseIds);

    /**
     * @param  array     $ids
     * @return array[]
     */
    public function findCourseSetsByIds(array $ids);

    /**
     * @param  array        $conditions
     * @param  array|string $orderBys
     * @param  integer      $start
     * @param  integer      $limit
     * @return array[]
     */
    public function searchCourseSets(array $conditions, $orderBys, $start, $limit);

    /**
     * @param  array     $conditions
     * @return integer
     */
    public function countCourseSets(array $conditions);

    public function getCourseSet($id);

    public function createCourseSet($courseSet);

    /**
     * 复制课程到班级
     * @param  int     $classroomId
     * @param  int     $courseSetId   要复制的课程
     * @param  int     $courseId      要复制的教学计划
     * @return mixed
     */
    public function copyCourseSet($classroomId, $courseSetId, $courseId);

    public function updateCourseSet($id, $fields);

    public function updateCourseSetDetail($id, $fields);

    public function changeCourseSetCover($id, $fields);

    public function deleteCourseSet($id);

    /**
     * @param  integer   $userId
     * @param  bool      $onlyPublished 是否只需要发布的课程
     * @return array[]
     */
    public function findTeachingCourseSetsByUserId($userId, $onlyPublished = true);

    /**
     * @param  integer   $userId
     * @return array[]
     */
    public function findLearnCourseSetsByUserId($userId);

    /**
     * @param  array     $ids
     * @return array[]
     */
    public function findPublicCourseSetsByIds(array $ids);

    /**
     * @param  int       $userId
     * @return integer
     */
    public function countUserFavorites($userId);

    /**
     * @param  int       $userId
     * @param  int       $start
     * @param  int       $limit
     * @return array[]
     */
    public function searchUserFavorites($userId, $start, $limit);

    /**
     * 更新课程统计属性
     *
     * 如: 学员数、笔记数、评价数量
     *
     * @param  $id
     * @param  array   $fields
     * @return mixed
     */
    public function updateCourseSetStatistics($id, array $fields);

    public function publishCourseSet($id);

    public function closeCourseSet($id);

    public function findCourseSetsByParentIdAndLocked($parentId, $locked);

    public function recommendCourse($id, $number);

    public function cancelRecommendCourse($id);

    /**
     * 返回课程的营收额
     *
     * @param  array     $ids
     * @return array[]
     */
    public function findCourseSetIncomesByCourseSetIds(array $courseSetIds);

    public function batchUpdateOrg($courseSetIds, $orgCode);
}
