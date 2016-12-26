<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * Interface CourseMemberDao
 * @package Biz\Course\Dao
 * TODO course2.0 所有的api 需要重构，很多的api可以合并，还有名字不规范
 */
interface CourseMemberDao extends GeneralDaoInterface
{
    /**
     * @param $courseId
     * @param $userId
     * @return mixed
     * @before getMemberByCourseIdAndUserId
     */
    public function getByCourseIdAndUserId($courseId, $userId);

    /**
     * 用来替代各种命名复杂的关联表的列表查询
     *
     * @param  $conditions
     * @param  $orderBy
     * @param  $start
     * @param  $limit
     * @return mixed
     */
    public function searchMemberFetchCourse($conditions, $orderBy, $start, $limit);

    /**
     * 用来替代各种命名复杂的关联表的数量查询
     * @param  $conditions
     * @return mixed
     */
    public function countMemberFetchCourse($conditions);

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);


    /**
     * @param $courseIds
     * @return mixed
     * @before getMembersByCourseIds
     * @before findMembersByCourseIds
     */
    public function findByCourseIds($courseIds);

    /**
     * @param $userId
     * @param $role
     * @param $start
     * @param $limit
     * @param bool $onlyPublished
     * @return mixed
     * @before findMembersByUserIdAndRole
     */
    public function findByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true);

    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true); //

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true);

    public function findMemberCountNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true); //

    public function findMembersByCourseIdAndRole($courseId, $role);

    public function findMembersByUserIdAndJoinType($userId, $joinedType);

    public function searchMemberIds($conditions, $orderBy, $start, $limit);

    public function updateMembers($conditions, $updateFields);

    public function deleteMemberByCourseIdAndRole($courseId, $role);

    public function findCourseMembersByUserId($userId);

    public function deleteMembersByCourseId($courseId);

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds);

    public function findByCourseId($courseId);

    public function findAllMemberByUserIdAndRole($userId, $role, $onlyPublished = true);
}
