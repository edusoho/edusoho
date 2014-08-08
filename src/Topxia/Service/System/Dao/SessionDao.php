<?php 
namespace Topxia\Service\System\Dao;

interface SessionDao
{
	public function getOnlineCount($retentionTime);
}