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
        $builder->addOrderBy($sort[0], $sort[1]);

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

	protected function createLogQueryBuilder($conditions)
	{
		$conditions = array_filter($conditions);


		$builder = $this->createDynamicQueryBuilder($conditions)
			->andWhere('module = :module')
			->andWhere('action = :action')
			->andWhere('level = :level')
			->andWhere('userId = :userId')
			->andWhere('createdTime > :startDateTime')
			->andWhere('createdTime < :endDateTime')
			->andWhere('userId IN ( :userIds )');

		return $builder;
	}

	public function analysisLoginNumByTime($startTime,$endTime)
	{
        $sql="SELECT count(distinct userid)  as num FROM `{$this->table}` WHERE `action`='login_success' and  `createdTime`>= ? and `createdTime`<= ?  ";
		return $this->getConnection()->fetchColumn($sql, array($startTime, $endTime));
	}

	public function analysisLoginDataByTime($startTime,$endTime)
	{
		$sql="SELECT count(distinct userid) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE `action`='login_success' and `createdTime`>= ? and `createdTime`<= ? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";
		return $this->getConnection()->fetchAll($sql, array($startTime, $endTime));
	}
}