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


	protected function addLog($module, $action, $message, $level)
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