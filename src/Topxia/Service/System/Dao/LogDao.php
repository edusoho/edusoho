<?php

namespace Topxia\Service\System\Dao;

interface LogDao
{
	public function addLog($log);

	public function searchLogs($conditions, $sort, $start, $limit);
	
	public function searchLogCount($conditions);

	public function findLoginRecordCountByUserId ($userId);

	public function findLoginRecordByUserId($userId, $start, $limit);
}