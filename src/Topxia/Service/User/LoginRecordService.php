<?php

namespace Topxia\Service\User;

interface LoginRecordService
{
	public function searchLoginRecordCount(array $conditions);

	public function searchLoginRecord(array $conditions, array $orderBy, $start, $limit);

	public function findLoginRecordCountByUserId ($userId);

	public function findLoginRecordByUserId ($userId, $start, $limit);
}