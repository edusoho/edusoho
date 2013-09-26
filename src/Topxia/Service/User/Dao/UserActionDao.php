<?php
namespace Topxia\Service\User\Dao;

interface UserActionDao
{
	public function getUser($id);

	public function findUsersByIds(array $ids);

    public function searchUsers($conditions,array $orderBy, $start, $limit);

    public function searchUserCount($conditions);

    public function addUser($user);

	public function updateUser($id, $fields);


}