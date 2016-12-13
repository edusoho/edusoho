<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMemberDao extends GeneralDaoInterface
{
    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findStudentsByCourseId($courseId);

    public function getMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned);

    public function getMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned);

    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit);

    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit);

}
