<?php
namespace Topxia\Service\Common;

use Doctrine\DBAL\Query\QueryBuilder;
use Topxia\Service\Common\Connection;

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

        if ($this->isInCondition($where)) {
            $where = $this->whereIn($where);
            if(!$where) {
                return $this;
            }
        }

        return parent::andWhere($where);
    }

    public function orWhere($where)
    {
        if (!$this->isWhereInConditions($where)) {
            return $this;
        }

        if ($this->isInCondition($where)) {
            $where = $this->whereIn($where);
            if(!$where) {
                return $this;
            }
        }

        return parent::orWhere($where);
    }

    public function andStaticWhere($where)
    {
        return parent::andWhere($where);
    }

    protected function whereIn($where)
    {
        $conditionName = $this->getConditionName($where);

        if (empty($this->conditions[$conditionName]) || !is_array($this->conditions[$conditionName])) {
            return false;
        }

        $this->conditions[$conditionName] = array_unique($this->conditions[$conditionName]);

        $marks = array();

        foreach (array_values($this->conditions[$conditionName]) as $index => $value) {
            $marks[]                                       = ":{$conditionName}_{$index}";
            $this->conditions["{$conditionName}_{$index}"] = $value;
        }

        $where = str_replace(":{$conditionName}", join(',', $marks), $where);

        return $where;
    }

    public function execute()
    {
        foreach ($this->conditions as $field => $value) {
            $this->setParameter(":{$field}", $value);
        }

        return parent::execute();
    }

    protected function isInCondition($where)
    {
        $matched = preg_match('/\s+(IN)\s+/', $where, $matches);

        if (empty($matched)) {
            return false;
        } else {
            return true;
        }
    }

    protected function getConditionName($where)
    {
        $matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);

        if (empty($matched)) {
            return false;
        }

        return $matches[1];
    }

    protected function isWhereInConditions($where)
    {
        $conditionName = $this->getConditionName($where);

        if (!$conditionName) {
            return false;
        }

        return array_key_exists($conditionName, $this->conditions) && !is_null($this->conditions[$conditionName]);
    }
}
