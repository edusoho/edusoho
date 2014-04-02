<?php

namespace Vip\Service\Vip\Dao;

interface VipHistoryDao
{
	public function getMemberHistory($memberId);

	public function searchMembersHistories($conditions, $orderBy, $start, $limit);

	public function searchMembersHistoriesCount($conditions);

	public function addMemberHistory($member);
}