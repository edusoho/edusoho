<?php

namespace Biz\User\Dao;

interface UserSecureQuestionDao
{
    public function getUserSecureQuestionsByUserId($userId);
    public function addOneUserSecureQuestion($filedsWithUserIdAndQuestionNumAndQuestionAndHashedAnswerAndAnswerSalt);
}
