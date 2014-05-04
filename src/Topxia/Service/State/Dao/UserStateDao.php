<?php

namespace Topxia\Service\State\Dao;

interface UserStateDao
{
	public function getUserState($id);

	public function findUserStatesByIds(array $ids);

    public function searchUserStates($conditions, $orderBy, $start, $limit);

    public function searchUserStateCount($conditions);

    public function addUserState($userState);

	public function updateUserState($id, $fields);

	

}