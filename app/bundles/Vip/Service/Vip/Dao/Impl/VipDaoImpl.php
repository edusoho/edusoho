<?php

namespace Vip\Service\Vip\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Vip\Service\Vip\Dao\VipDao;
use PDO;

class VipDaoImpl extends BaseDao implements VipDao
{
	protected $table = 'vip';

    public function getMember($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getMemberByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : null;
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createMemberQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchMembersCount($conditions)
    {
        $builder = $this->createMemberQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createMemberQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'member')
            ->andWhere('levelId = :level')
            ->andWhere('userId = :userId')
            ->andWhere('deadline <= :deadlineMoreThan')
            ->andWhere('deadline > :deadlineLessThan');
    }

	public function deleteMemberByUserId($userId)
	{
        $affected = $this->getConnection()->delete($this->table, $userId);
        if ($affected <= 0) {
            throw $this->createDaoException('delete member error.');
        }
        return true;
	}

	public function addMember($member)
	{
        $affected = $this->getConnection()->insert($this->table, $member);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert member error.');
        }
        return $this->getMemberByUserId($member['userId']);
	}

	public function updateMember($id, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('userId' => $id));
        return $this->getMember($id);
	}
}