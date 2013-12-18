<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\LogDao;

class LogDaoImpl extends BaseDao implements LogDao 
{
	protected $table = 'log';

	public function addLog($log)
	{
        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert log error.');
        }
	}

	public function searchLogs($conditions, $sort, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);
		$builder = $this->createLogQueryBuilder($conditions)
	        ->select('*')
	        ->from($this->table, $this->table);
        $builder->addOrderBy($sort, "DESC");

		$builder->setFirstResult($start)->setMaxResults($limit);    
       	return $builder->execute()->fetchAll() ? : array();
	}

	public function searchLogCount($conditions)
	{
		$builder = $this->createLogQueryBuilder($conditions)
			->select('count(`id`) AS count')
			->from($this->table, $this->table);
		return $builder->execute()->fetchColumn(0);
	}

	public function findLoginRecordCountByUserId ($userId)
	{
		$sql = "SELECT COUNT(id) FROM {$this->table} WHERE userId = ?";
    	$loginRecordCount = $this->getConnection()->fetchAll($sql, array($userId));
    	return $loginRecordCount[0]['COUNT(id)'];
	}

	public function findLoginRecordByUserId($userId, $start, $limit)
    {
    	$this->filterStartLimit($start, $limit);
    	$sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

	protected function createLogQueryBuilder($conditions)
	{
		$conditions = array_filter($conditions);
		return $this->createDynamicQueryBuilder($conditions)
			->andWhere('module = :module')
			->andWhere('action = :action')
			->andWhere('level = :level')
			->andWhere('userId = :userId')
			->andWhere('createdTime > :startDateTime')
			->andWhere('createdTime < :endDateTime');
	}
}