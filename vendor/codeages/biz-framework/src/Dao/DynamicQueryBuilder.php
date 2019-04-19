<?php

namespace Codeages\Biz\Framework\Dao;

use Doctrine\DBAL\Query\QueryBuilder;

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

        if ($likeType = $this->matchLikeCondition($where)) {
            return $this->addWhereLike($where, $likeType);
        }

        return parent::andWhere($where);
    }

    public function andStaticWhere($where)
    {
        return parent::andWhere($where);
    }

    private function addWhereLike($where, $likeType)
    {
        $conditionName = $this->getConditionName($where);

        //PRE_LIKE
        if ('pre_like' == $likeType) {
            $where = preg_replace('/pre_like/i', 'LIKE', $where, 1);
            $this->conditions[$conditionName] = "{$this->conditions[$conditionName]}%";
        } elseif ('suf_like' == $likeType) {
            $where = preg_replace('/suf_like/i', 'LIKE', $where, 1);
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}";
        } else {
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}%";
        }

        return parent::andWhere($where);
    }

    public function execute()
    {
        foreach ($this->conditions as $field => $value) {
            if (is_array($value)) {
                $this->setParameter(":{$field}", $value, Connection::PARAM_STR_ARRAY);
            } else {
                $this->setParameter(":{$field}", $value);
            }
        }

        return parent::execute();
    }

    private function matchLikeCondition($where)
    {
        $matched = preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    private function getConditionName($where)
    {
        $matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);
        if (!$matched) {
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