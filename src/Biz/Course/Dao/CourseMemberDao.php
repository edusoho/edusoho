<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * Interface CourseMemberDao
 * TODO course2.0 所有的api 需要重构，很多的api可以合并，还有名字不规范.
 */
interface CourseMemberDao extends GeneralDaoInterface
{
    const TABLE_NAME = 'course_member';

    /**
     * @before getMemberByCourseIdAndUserId
     *
     * @param  $courseId
     * @param  $userId
     *
     * @return mixed
     */
    public function getByCourseIdAndUserId($courseId, $userId);

    public function countLearningMembers($conditions);

    public function findLearningMembers($conditions, $start, $limit);

    public function countLearnedMembers($conditions);

    public function findLearnedMembers($conditions, $start, $limit);

    public function countUserLearnCourses($userId);

    public function findUserLearnCourseIds($userId);

    public function findByIds($ids);

    public function findLearnedByCourseIdAndUserId($courseId, $userId);

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);

    /**
     * @before getMembersByCourseIds
     * @before findMembersByCourseIds
     *
     * @param  $courseIds
     *
     * @return mixed
     */
    public function findByCourseIds($courseIds);

    public function findLastLearnTimeRecordStudents($userIds);

    /**
     * @before findMembersByUserIdAndRole
     *
     * @param  $userId
     * @param  $role
     *
     * @return mixed
     */
    public function findByUserIdAndRole($userId, $role);

    /**
     * @param $userId
     * @param $courseSetId
     * @param $role
     *
     * @return array
     */
    public function findByUserIdAndCourseSetIdAndRole($userId, $courseSetId, $role);

    public function findByConditionsGroupByUserId($conditions, $orderBy, $offset, $limit);

    /**
     * @param $userId
     * @param $role
     * @param $start
     * @param $limit
     * @param bool $onlyPublished
     * @param bool $filterReservation
     *
     * @return mixed
     */
    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true, $filterReservation = false);

    public function findByCourseIdAndRole($courseId, $role);

    public function findByCourseSetIdAndRole($courseSetId, $role);

    public function findByUserIdAndJoinType($userId, $joinedType);

    public function searchMemberIds($conditions, $orderBys, $start, $limit);

    public function updateMembers($conditions, $updateFields);

    public function deleteByCourseIdAndRole($courseId, $role);

    public function deleteByCourseId($courseId);

    public function findByUserIdAndCourseIds($userId, $courseIds);

    public function findByCourseId($courseId);

    public function findUserIdsByCourseId($courseId);

    public function findByUserId($userId);

    public function countThreadsByCourseIdAndUserId($courseId, $userId, $type = 'discussion');

    public function countActivitiesByCourseIdAndUserId($courseId, $userId);

    public function countPostsByCourseIdAndUserId($courseId, $userId);

    public function countMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned);

    public function countMemberNotInClassroomByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $filterReservation = false);

    public function countMemberNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true);

    public function findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit);

    public function findMembersNotInClassroomByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit, $filterReservation = false);

    public function countMemberByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned);

    public function countMemberByUserIdAndRoleAndIsLearned($userId, $role, $isLearned);

    public function findMembersNotInClassroomByUserIdAndRoleAndType($userId, $role, $type, $start, $limit, $onlyPublished = true);

    public function updateByClassroomIdAndUserId($classroomId, $userId, array $fields);

    public function updateByClassroomId($classroomId, array $fields);

    public function searchMemberCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format = '%Y-%m-%d');
}
