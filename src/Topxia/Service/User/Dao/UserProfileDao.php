<?php

namespace Topxia\Service\User\Dao;

interface UserProfileDao
{
	public function getProfile($id);

	public function addProfile($profile);

	public function updateProfile($id, $profile);

    public function findProfilesByIds(array $ids);

    public function dropFieldData($fieldName);
}