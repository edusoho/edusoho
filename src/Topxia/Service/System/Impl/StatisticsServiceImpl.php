<?php 

namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\StatisticsService;

class StatisticsServiceImpl extends BaseService implements StatisticsService
{
	public function getOnlineCount($retentionTime)
	{
		if($this->getKernel()->getRedis()){
			$keys = $this->getKernel()->getRedis()->getKeys("session:online*");
			return count($keys);
		}

		return $this->getSessionDao()->getOnlineCount($retentionTime);
	}

	public function getloginCount($retentionTime)
	{
		if($this->getKernel()->getRedis()){
			$keys = $this->getKernel()->getRedis()->getKeys("session:logined*");
			return count($keys);
		}
		return $this->getSessionDao()->getLoginCount($retentionTime);	
	}

	protected function getSessionDao()
	{
		return $this->createDao('System.SessionDao');
	}
}