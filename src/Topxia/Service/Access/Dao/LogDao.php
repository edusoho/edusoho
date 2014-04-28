<?php

namespace Topxia\Service\Access\Dao;

interface LogDao
{
	public function getLog($id);

	public function findLogsByIds(array $ids);

    public function searchLogs($conditions, $orderBy, $start, $limit);

    public function searchLogCount($conditions);

    public function addLog($log);

	public function updateLog($id, $fields);


}