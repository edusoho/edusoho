<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\LoginRecordService;
use Topxia\Common\ConvertIpToolkit;

class LoginRecordServiceImpl extends BaseService implements LoginRecordService
{
	public function searchLoginRecordCount(array $conditions)
	{
		$conditions = $this->filterConditions($conditions);

		return $this->getLogDao()->searchLogCount($conditions);
	}

	public function searchLoginRecord(array $conditions, array $orderBy, $start, $limit)
	{
		$conditions = $this->filterConditions($conditions);

		$logRecords = $this->getLogDao()->searchLogs($conditions, $orderBy[0], $start, $limit);
		return ConvertIpToolkit::ConvertIps($logRecords);
	}

	public function findLoginRecordCountByUserId ($userId)
	{
		$user = $this->getUserDao()->getUser($userId);
		if(empty($user)){
			throw $this->createServiceException("ERROR! The User Not Exist!");
		}

		return $this->getLogDao()->findLoginRecordCountByUserId($userId);
	}

	public function findLoginRecordByUserId ($userId, $start, $limit)
	{
		$logRecords = $this->getLogDao()->findLoginRecordByUserId($userId, $start, $limit);
		return ConvertIpToolkit::ConvertIps($logRecords);
	}

	private function filterConditions($conditions)
	{
		if (isset($conditions['nickname'])) {
			$user = $this->getUserDao()->findUserByNickname($conditions['nickname']);
			if ($user){
				$conditions['userId'] = $user['id'];
			}
			unset($conditions['nickname']);
		}
		if (isset($conditions['email'])) {
			$user = $this->getUserDao()->findUserByEmail($conditions['email']);
			if ($user) {
				$conditions['userId'] = $user['id'];
			}
			unset($conditions['email']);
		}
		if (isset($conditions['startDateTime'])) {
			$conditions['startDateTime'] = strtotime($conditions['startDateTime']);
		}
		if (isset($conditions['endDateTime'])) {
			$conditions['endDateTime'] = strtotime($conditions['endDateTime']);
		}
		return $conditions;
	}

	private function getLogDao()
	{
		return $this->createDao('System.LogDao');
	}

	private function getUserDao()
	{
		return $this->createDao('User.UserDao');
	}
}