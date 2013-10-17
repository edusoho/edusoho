<?php

namespace Topxia\Service\Upgrade\Dao;

interface UpgradeLogDao 
{
	
	public function getLog($id);

	public function addLog($log);

	public function updateLog($id,$log);

	public function getUpdateLogByEnameAndVersion($ename,$tov);

	public function searchLogs($conditions, $start, $limit);

	public function searchLogCount($conditions);
	// 日志操作一般不设计更新,所以忽略update操作
}
