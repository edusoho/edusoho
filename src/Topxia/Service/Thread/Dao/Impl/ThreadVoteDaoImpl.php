<?php

namespace Topxia\Service\Thread\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Thread\Dao\ThreadVoteDao;

class ThreadVoteDaoImpl extends BaseDao implements ThreadVoteDao
{

    protected $table = 'thread_vote';

    public function getVote($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getVoteByThreadIdAndPostIdAndUserId($threadId, $postId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND postId =? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($threadId, $postId, $userId)) ? : null;
    }

    public function addVote($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert vote error.');
        }
        return $this->getVote($this->getConnection()->lastInsertId());
    }

}