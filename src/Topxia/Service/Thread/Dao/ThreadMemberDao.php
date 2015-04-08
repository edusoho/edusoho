<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadMemberDao
{

    public function findActivityMembersByThreadId($threadId);

}