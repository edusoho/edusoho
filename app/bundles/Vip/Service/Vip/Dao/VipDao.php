<?php

namespace Vip\Service\Vip\Dao;

interface VipDao
{
	public function getMemberByUserId($userId);
	
	public function searchMembers($conditions, $orderBy, $start, $limit);

	public function searchMembersCount($conditions);

	public function deleteMemberByUserId($userId);

	public function addMember($member);

	public function updateMember($id, $fields);
}