<?php

namespace Topxia\Service\User;

interface LoginRecordService
{
	public function searchLoginRecordCount(array $conditions);

	public function searchLoginRecord(array $conditions, array $orderBy, $start, $limit);

	public function findLoginRecordCountById ($id);

	public function findLoginRecordById ($id, $start, $limit);
}