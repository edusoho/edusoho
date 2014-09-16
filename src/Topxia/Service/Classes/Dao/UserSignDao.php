<?php

namespace Topxia\Service\Classes\Dao;

interface UserSignDao
{
	public function addUserSign($userSign);

	public function getUserSign($id);

	public function findUserSignByUserIdAndPeriod($userId, $startTime, $EndTime);
}