<?php

namespace Member\Service\Member\Dao;

interface MemberHistoryDao
{
	public function getMemberHistory($memberId);

	public function searchMembersHistories($conditions, $orderBy, $start, $limit);

	public function searchMembersHistoriesCount($conditions);

	public function addMemberHistory($member);
}