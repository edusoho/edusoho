<?php

namespace Member\Service\Member\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Member\Service\Member\Dao\MemberHistoryDao;

class MemberHistoryDaoImpl extends BaseDao implements MemberHistoryDao
{
	protected $table = 'member_history';

	public function getMemberHistory($memberId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchColumn($sql,array($memberId)) ? : null;
    }

    public function searchMembersHistories($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createMemberHistoryQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchMembersHistoriesCount($conditions)
    {
        $builder = $this->createMemberHistoryQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addMemberHistory($member_history)
    {
        $affected = $this->getConnection()->insert($this->table,$member_history);
        if($affected <= 0){
            throw $this->createDaoException('Insert member_history error.');
        }
        return $this->getMemberHistory($this->getConnection()->lastInsertId());
    }

    private function createMemberHistoryQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'member_history')
            ->andWhere('levelId = :level')
            ->andWhere('userId = :userId')
            ->andWhere('userNickname =:nickname')
            ->andWhere('boughtType =:boughtType');
    }
}