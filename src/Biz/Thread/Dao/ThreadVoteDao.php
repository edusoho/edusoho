<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadVoteDao extends GeneralDaoInterface
{
    public function getVoteByThreadIdAndPostIdAndUserId($threadId, $postId, $userId);
}
