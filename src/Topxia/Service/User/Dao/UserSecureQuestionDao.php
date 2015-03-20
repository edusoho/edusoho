<?php

namespace Topxia\Service\User\Dao;

interface UserSecureQuestionDao
{
	public function getUserSecureQuestionsByUserId($userId);
	public function addOneUserSecureQuestion($filedsWithUserIdAndQuestionNumAndQuestionAndHashedAnswerAndAnswerSalt);
}