<?php

namespace Topxia\Service\User\Dao;

interface UserDao
{
	public function getUser($id);

	public function findUserByEmail($email);

	public function findUserByNickname($nickname);

	public function findUsersByIds(array $ids);

	public function searchUsers($conditions, $orderBy, $start, $limit);

	public function searchUserCount($conditions);

	public function addUser($user);

	public function updateUser($id, $fields);

	public function waveCounterById($id, $name, $number);

	public function clearCounterById($id, $name);

	public function analysisRegisterDataByTime($startTime,$endTime);

}