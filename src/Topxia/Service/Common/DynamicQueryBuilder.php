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

        if($this->isInCondition($where)) {
            $conditionName = $this->getConditionName($where);
            return $this->addWhereIn($where, $conditionName, $this->conditions[$conditionName]);
        } else {
            return parent::andWhere($where);
        }

    }

    public function andStaticWhere($where)
    {
    	return parent::andWhere($where);
    }

    private function addWhereIn($where, $conditionName, $params)
    {
        if(count($params) == 0) {
            return $this;
        }
        $sqlWhere = "";
        foreach ($params as $index => $param) {
            if(count($params) == ($index+1)) {
                $sqlWhere .= ":{$conditionName}_".$index;
            } else {
                $sqlWhere .= ":{$conditionName}_".$index.",";
            }

            $this->conditions[$conditionName."_".$index] = $param;
        }
        $sqlWhere .= "";
        
        $where = str_replace(":".$conditionName, $sqlWhere, $where);

        return parent::andWhere($where);
    }

    public function execute()
    {
    	foreach ($this->conditions as $field => $value) {
    		$this->setParameter(":{$field}", $value);
    	}
    	return parent::execute();
    }

    private function isInCondition($where)
    {
        $matched = preg_match('/ (IN) /', $where, $matches);
        if (empty($matched)) {
            return false;
        } else {
            return true;
        }
    }

    private function getConditionName($where) {
        $matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);
        if (empty($matched)) {
            return false;
        }
        return $matches[1];
    }

    private function isWhereInConditions($where)
    {
        $conditionName = $this->getConditionName($where);
    	if (!$conditionName) {
    		return false;
    	}

        return array_key_exists($conditionName, $this->conditions) && !is_null($this->conditions[$conditionName]);
    }
}