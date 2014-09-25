<?php

namespace Topxia\Service\Sign\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Sign\Dao\SignUserLogDao;

class SignUserLogDaoImpl extends BaseDao implements SignUserLogDao
{
	protected $table = 'sign_user_log';

	public function addSignLog($signLog)
	{
        $affected = $this->getConnection()->insert($this->table, $signLog);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user sign error.');
        }
        return $this->getClassMemberSign($this->getConnection()->lastInsertId());
	}

	public function getSignLOg($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $EndTime)
	{
		$sql ="SELECT * FROM {$this->table} WHERE userId = ? AND targetType = ? AND targetId = ? AND createdTime > ? AND createdTime < ?;";
        return $this->getConnection()->fetchAll($sql, array($userId, $targetType, $targetId, $startTime, $EndTime)) ? : null;
	}
}
