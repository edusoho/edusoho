<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadMemberDao extends GeneralDaoInterface
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
