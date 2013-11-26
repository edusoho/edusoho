<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserApprovalDao;

class UserApprovalDaoImpl extends BaseDao implements UserApprovalDao
{
	protected $table = 'user_approval';

	public function addApproval($approval)
	{
		$affected = $this->getConnection()->insert($this->table, $approval);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user approval error.');
        }
        return $this->getApproval($this->getConnection()->lastInsertId());
	}

	public function getApproval($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getApprovalByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId));
	}
}