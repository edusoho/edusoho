<?php
namespace Topxia\Service\State;

interface UserStateService
{

	public function getUserState($id);

	public function findUserStatesByIds(array $ids);

	public function createUserState($userState);

	public function searchUserStates($conditions,$sort,$start,$limit);

	public function searchUserStateCount($conditions);

	

}