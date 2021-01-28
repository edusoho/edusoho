<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadMemberDao extends GeneralDaoInterface
{
    public function getMemberByThreadIdAndUserId($threadId, $userId);

    public function deleteMembersByThreadId($threadId);
}
