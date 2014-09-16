<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\UserSignDao;

class UserSignDaoImpl extends BaseDao implements UserSignDao
{
	protected $table = 'user_sign';

	public function addUserSign($userSign)
	{
        $affected = $this->getConnection()->insert($this->table, $userSign);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user sign error.');
        }
        return $this->getUserSign($this->getConnection()->lastInsertId());
	}

	public function getUserSign($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findUserSignByUserIdAndPeriod($userId, $startTime, $EndTime)
	{
		$sql ="SELECT * FROM {$this->table} WHERE userId = ? and createdTime > ? and createdTime < ?;";
        return $this->getConnection()->fetchAll($sql, array($userId, $startTime, $EndTime)) ? : null;
	}
}
