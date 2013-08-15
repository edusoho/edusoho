<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\LogDao;

class LogDaoImpl extends BaseDao implements LogDao 
{
	protected $table = 'log';

	public function addLog($log)
	{
		return $this->insert($log);
	}

	public function searchLogs($conditions, $sorts, $start, $limit)
	{
		$builder = $this->createLogQueryBuilder($conditions)
				        ->select('*')
				        ->from($this->table, $this->table);

        foreach ($sorts as $field => $value) {
        	$builder->addOrderBy($field, $value);
        }

		$builder->setFirstResult($start)
				->setMaxResults($limit);    
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
		if (isset($conditions['module'])) {
			$conditions['module'] = "%{$conditions['module']}%";
		}

		if (isset($conditions['action'])) {
			$conditions['action'] = "%{$conditions['action']}%";
		}

		if (isset($conditions['message'])) {
			$conditions['message'] = "%{$conditions['message']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions);

		if ($conditions) {
	        foreach ( $conditions as $field => $value ) {
	        	if(in_array($field, array('module', 'message', 'action'))) {
	        		$builder->andWhere("{$field} LIKE :{$field}");

	        	} else if (in_array($field, array('startDateTime', 'endDateTime'))) {
	        		if ($field == 'startDateTime') {
	        			$builder->andWhere("createdTime >= :{$field}");
	        		} else {
	        			$builder->andWhere("createdTime <= :{$field}");
	        		}
	        	}else {
	        		$builder->andWhere("{$field} = :{$field}");
	        	}
	        }
		}

		return $builder;
	}


}