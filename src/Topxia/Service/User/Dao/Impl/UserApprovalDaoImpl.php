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

	public function updateApproval($id, $fields)
	{
		$this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getApproval($id);
	}

	public function getLastestApprovalByUserIdAndStatus($userId, $status)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND status = ? ORDER BY createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $status));
	}

	public function findApprovalsByUserIds($userIds)
	{
		if(empty($userIds)){
            return array();
        }
        
        $marks = str_repeat('?,', count($userIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE userId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $userIds);
	}
}