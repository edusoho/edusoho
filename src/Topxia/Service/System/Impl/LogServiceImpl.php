<?php

namespace Topxia\Service\System\Impl;

use Topxia\Service\System\LogService;
use Topxia\Service\Common\BaseService;

class LogServiceImpl extends BaseService implements  LogService
{	

	public function info($module, $action, $message)
	{
		return $this->addLog('info', $module, $action, $message);
	}

	public function warning($module, $action, $message)
	{
		return $this->addLog('warning', $module, $action, $message);
	}
	
	public function error($module, $action, $message)
	{
		return $this->addLog('error', $module, $action, $message);
	}

	public function searchLogs($conditions, $sort, $start, $limit)
	{
		$conditions = $this->prepareSearchConditions($conditions);

		switch ($sort) {
			case 'created':
				$sort = 'createdTime';
				break;
			
			default:
				throw $this->createServiceException('参数sort不正确。');
				break;
		}

		return $this->getLogDao()->searchLogs($conditions, $sort, $start, $limit);
	}

	public function searchLogCount($conditions)
	{
		$conditions = $this->prepareSearchConditions($conditions);
		return $this->getLogDao()->searchLogCount($conditions);
	}

	protected function addLog($level, $module, $action, $message)
	{
		return $this->getLogDao()->addLog(array(
			'module' => $module,
			'action' => $action,
			'message' => $message,
			'userId' => $this->getCurrentUser()->id,
			'ip' => $this->getCurrentUser()->currentIp,
			'createdTime' => time(),
			'level' => $level,
		));		
	}

	protected function getLogDao()
	{
		return $this->createDao('System.LogDao');
	}	

	protected function getUserService()
	{
		return $this->createService('User.UserService');
	}

	private function prepareSearchConditions($conditions)
	{
        if (!empty($conditions['nickname'])) {
            $existsUser = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $userId = $existsUser ? $existsUser['id'] : -1;
            $conditions['userId'] = $userId;
            unset($conditions['nickname']);
        }

        if ($conditions['startDateTime'] && $conditions['endDateTime']) {
			$conditions['startDateTime'] = strtotime($conditions['startDateTime']);
			$conditions['endDateTime'] = strtotime($conditions['endDateTime']); 
        } else {
        	unset($conditions['startDateTime']);
        	unset($conditions['endDateTime']);
        }

        if (in_array($conditions['level'], array('info', 'warning', 'error'))) {
        	$conditions['level'] = $conditions['level'];
        } else {
        	unset($conditions['level']);
        }

		return $conditions;
	}
}