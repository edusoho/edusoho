<?php

namespace Topxia\Service\Classes\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classes\Dao\UserSignRelatedDao;

class UserSignRelatedDaoImpl extends BaseDao implements UserSignRelatedDao
{
	protected $table = 'user_sign_related';

	public function addUserSignRelated($userSignRelated)
	{
        $affected = $this->getConnection()->insert($this->table, $userSign);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert class sign related error.');
        }
        return $this->getUserSignRelated($this->getConnection()->lastInsertId());
	}

	public function getUserSignRelated($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getUserSignRelatedByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : null;
	}

	public function updateUserSignRelated($userId, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('userId' => $userId));
        return $this->getUserSignRelatedByUserId($userId);
	}
}
