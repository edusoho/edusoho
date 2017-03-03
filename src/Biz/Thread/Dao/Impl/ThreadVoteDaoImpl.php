<?php

namespace Biz\Thread\Dao\Impl;

use Biz\Thread\Dao\ThreadVoteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadVoteDaoImpl extends GeneralDaoImpl implements ThreadVoteDao
{
    protected $table = 'thread_vote';

    public function getVoteByThreadIdAndPostIdAndUserId($threadId, $postId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND postId =? AND userId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($threadId, $postId, $userId)) ?: null;
    }

    public function declares()
    {
        return array();
    }
}
