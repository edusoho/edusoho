<?php
namespace Topxia\Service\Common;

use Doctrine\DBAL\Query\QueryBuilder,
	Doctrine\DBAL\Connection;

class DynamicQueryBuilder extends QueryBuilder
{
	protected $conditions;

    public function __construct(Connection $connection, $conditions)
    {
    	parent::__construct($connection);
    	$this->conditions = $conditions;
    }

    public function where($where)
    {
    	if (!$this->isWhereInConditions($where)) {
    		return $this;
    	}
    	return parent::where($where);
    }

    public function andWhere($where)
    {
    	if (!$this->isWhereInConditions($where)) {
    		return $this;
    	}
        return parent::andWhere($where);
    }

    public function andStaticWhere($where)
    {
    	return parent::andWhere($where);
    }

    public function execute()
    {
    	foreach ($this->conditions as $field => $value) {
    		$this->setParameter(":{$field}", $value);
    	}
    	return parent::execute();
    }

    private function isWhereInConditions($where)
    {
    	$matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);
    	if (empty($matched)) {
    		return false;
    	}
        return array_key_exists($matches[1], $this->conditions) && !is_null($this->conditions[$matches[1]]);
    }
}