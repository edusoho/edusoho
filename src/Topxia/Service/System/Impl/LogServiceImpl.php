<?php

namespace Topxia\Service\System\Impl;

use Topxia\Service\System\LogService;
use Topxia\Service\Common\BaseService;

use Symfony\Component\HttpFoundation\Request;


class LogServiceImpl extends BaseService implements  LogService
{	

	public function info($module, $action, $message)
	{
		return $this->addLog($module, $action, $message, 'info');
	}


	public function warning($module, $action, $message)
	{
		return $this->addLog($module, $action, $message, 'warning');
	}
	

	public function error($module, $action, $message)
	{
		return $this->addLog($module, $action, $message, 'error');
	}


	public function searchLogs($conditions, $sorts, $start, $limit)
	{
		return $this->getLogDao()->searchLogs($conditions, $sorts, $start, $limit);
	}


	public function searchLogCount($conditions)
	{
		return $this->getLogDao()->searchLogCount($conditions);
	}

	private function prepareSearchConditions($conditions)
	{
        if (!empty($conditions['nickname'])) {
            $existsUser = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $userId = $existsUser['id'] ? : -1;
            $conditions['userId'] = $userId;
        }

        if ($conditions['startDateTime'] && $conditions['endDateTime']) {
			$conditions['startDateTime'] = strtotime($conditions['startDateTime']);
			$conditions['endDateTime']   = strtotime($conditions['endDateTime']); 
        }

		return $conditions;
	}


	protected function addLog($level, $module, $action, $message)
	{
		return $this->getLogDao()->addLog(array(
			'module'		=> $module,
			'action'		=> $action,
			'message'		=> $message,
			'userId'		=> $this->getCurrentUser()->id,
			'ip'			=> $this->getCurrentUser()->currentIp,
			'createdTime'	=> time(),
			'level'			=> $level,
		));		
	}


	protected function getLogDao()
	{
		return $this->createDao('System.LogDao');
	}	
}