<?php

namespace Topxia\Service\Classes\Dao;

interface UserSignRelatedDao
{
	public function addUserSignRelated($userSignRelated);

	public function getUserSignRelated($id);

	public function updateUserSignRelated($userId, $fields);

	public function getUserSignRelatedByUserId($userId);
}