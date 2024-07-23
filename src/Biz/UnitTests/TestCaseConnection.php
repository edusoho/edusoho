<?php

namespace Biz\UnitTests;

use Codeages\Biz\Framework\Dao\Connection;

class TestCaseConnection extends Connection
{
    private $insertedTables = [];

    public function executeUpdate($query, array $params = [], array $types = [])
    {
        if ('insert' === strtolower(substr($query, 0, 6)) &&
            preg_match('/^insert\s+into\s+(\w+)\s+.*/i', $query, $matches)) {
            $this->insertedTables[] = $matches[1];
        }

        return parent::executeUpdate($query, $params, $types);
    }

    public function getInsertedTables()
    {
        return $this->insertedTables;
    }

    public function resetInsertedTables()
    {
        $this->insertedTables = [];
    }
}
