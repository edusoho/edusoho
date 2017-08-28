<?php

namespace Tests\Assert;

use PHPUnit\Framework\Constraint\Constraint;

class InDatabase extends Constraint
{
    protected $db;

    protected $table;

    protected $criteria;

    public function __construct($db, $table, $criteria)
    {
        parent::__construct();
        $this->db = $db;
        $this->table = $table;
        $this->criteria = $criteria;
    }

    protected function matches($other)
    {
        $builder = $this->db->createQueryBuilder();
        $builder->select('COUNT(*)')->from($this->table);

        $index = 0;
        foreach ($this->criteria as $key => $value) {
            $builder->andWhere("{$key} = ?");
            $builder->setParameter($index, $value);
            ++$index;
        }

        $count = $builder->execute()->fetch(\PDO::FETCH_COLUMN);

        return $count >= 1;
    }

    public function toString()
    {
        return sprintf('table %s has rows with criteria %s', $this->table, $this->exporter->export($this->criteria));
    }

    protected function failureDescription($other)
    {
        return $this->toString();
    }
}
