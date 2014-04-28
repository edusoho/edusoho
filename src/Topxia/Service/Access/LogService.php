<?php
namespace Topxia\Service\Access;

interface LogService
{

	public function getLog($id);

	public function findLogsByIds(array $ids);

	public function createLog($log);

	public function searchLogs($conditions,$sort,$start,$limit);

	public function searchLogCount($conditions);
	

}