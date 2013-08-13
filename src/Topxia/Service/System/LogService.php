<?php
namespace Topxia\Service\System;

interface LogService
{
	public function info($module, $action, $message);

	public function warning($module, $action, $message);
	
	public function error($module, $action, $message);

	public function searchLogs($conditions, $sort, $start, $limit);

	public function searchLogCount($conditions);
}