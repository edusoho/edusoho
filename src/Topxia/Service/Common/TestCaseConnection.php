<?php

namespace Topxia\Service\Common;

use Doctrine\DBAL\Connection;

class TestCaseConnection extends Connection
{
    private $_insertedTables = array();

    public function __construct($connection)
    {
        return parent::__construct(
            $connection->getParams(),
            $connection->getDriver(),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }

    public function insert($tableName, array $data, array $types = array())
    {
        $this->_insertedTables[] = $tableName;
        return parent::insert($tableName, $data, $types);
    }

    public function getInsertedTables()
    {
        return $this->_insertedTables;
    }

    public function resetInsertedTables()
    {
        $this->_insertedTables = array();
    }
}