<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadMemberDao
{
    public function getMember($memberId);

    public function getMemberByThreadIdAndUserId($threadId, $userId);

    public function addMember($member);

    public function deleteMember($memberId);

    public function deleteMembersByThreadId($threadId);

    public function findMembersCountByThreadId($threadId);

    public function findMembersByThreadId($threadId, $start, $limit);

    public function findMembersByThreadIdAndUserIds($threadId, $userIds);
}
