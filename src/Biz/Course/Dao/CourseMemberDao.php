<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMemberDao extends GeneralDaoInterface
{
    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findStudentsByCourseId($courseId);

    public function findTeachersByCourseId($courseId);

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
     * @deprecated
     */
    public function getMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned);

    /**
     * @deprecated
     */
    public function getMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned);

    /**
     * @deprecated
     */
    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit);

    /**
     * @deprecated
     */
    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit);


    /**
     * @param $courseIds
     * @return mixed
     * @before getMembersByCourseIds
     */
    public function findMembersByCourseIds($courseIds);

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true);

    public function findMembersNotInClassroomByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true); //

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true);

    public function findMemberCountNotInClassroomByUserIdAndRole($userId, $role, $onlyPublished = true); //

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit);

    public function findMemberCountByCourseIdAndRole($courseId, $role);

    public function findMembersByUserIdAndJoinType($userId, $joinedType);

    public function searchMemberIds($conditions, $orderBy, $start, $limit);

    public function updateMembers($conditions, $updateFields);

    public function deleteMemberByCourseIdAndUserId($courseId, $userId);

    public function deleteMemberByCourseIdAndRole($courseId, $role);

    public function findCourseMembersByUserId($userId);

    public function deleteMembersByCourseId($courseId);

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds);

    public function findMemberUserIdsByCourseId($courseId);

}
