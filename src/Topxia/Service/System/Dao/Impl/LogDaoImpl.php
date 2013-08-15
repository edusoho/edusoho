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

	public function searchLogs($conditions, $sorts, $start, $limit)
	{
		$builder = $this->createLogQueryBuilder($conditions)
			        ->select('*')
			        ->from($this->table, $this->table);

        foreach ($sorts as $field => $value) {
        	$builder->addOrderBy($field, $value);
        }

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
		$builder = $this->createDynamicQueryBuilder($conditions);
		$builder->andWhere('module = :module');
		$builder->andWhere('action = :action');
		$builder->andWhere('level = :level');
		$builder->andWhere('userId = :userId');
		$builder->andWhere('createdTime > :startDateTime');
		$builder->andWhere('createdTime < :endDateTime');
		return $builder;
	}
}