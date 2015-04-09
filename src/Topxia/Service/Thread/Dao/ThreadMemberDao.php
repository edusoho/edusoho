<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadMemberDao
{
    public function findMembersCountByThreadId($threadId);

    public function findMembersByThreadId($threadId);

}