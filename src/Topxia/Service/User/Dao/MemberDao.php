<?php

namespace Topxia\Service\User\Dao;

interface MemberDao
{
	public function searchMembers($conditions, $orderBy, $start, $limit);

	public function searchMembersCount($conditions);

	public function getMember($id);

	public function cancelMember($id);

	public function addMember();

	public function updateMember();

}