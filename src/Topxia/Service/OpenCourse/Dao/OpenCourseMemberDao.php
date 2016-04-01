<?php

namespace Topxia\Service\OpenCourse\Dao;

interface OpenCourseMemberDao
{
    public function getMember($id);

    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findMembersByCourseIds($courseIds);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function addMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit);

}
