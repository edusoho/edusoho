<?php

namespace Custom\Service\User\Dao;

interface CustomUserDao
{

	public function searchUsers($conditions, $orderBy, $start, $limit);

	public function searchUserCount($conditions);


}