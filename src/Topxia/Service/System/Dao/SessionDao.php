<?php 
namespace Topxia\Service\System\Dao;

interface SessionDao
{
	public function get($id);

	public function getSessionByUserId($userId);

	public function delete($id);
	
	public function deleteSessionByUserId($userId);

	public function getOnlineCount($retentionTime);
	
	public function getLoginCount($retentionTime);
}