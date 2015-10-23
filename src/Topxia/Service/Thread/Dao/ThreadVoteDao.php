<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadVoteDao
{
    public function getVote($id);

    public function getVoteByThreadIdAndPostIdAndUserId($threadId, $postId, $userId);

    public function addVote($fields);

}