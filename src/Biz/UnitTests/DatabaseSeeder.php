<?php

namespace Biz\UnitTests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;

abstract class DatabaseSeeder
{
    /**
     * @var Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    abstract public function run($isRun = true);

    protected function insertRows($table, array $rows, $isRun)
    {
        if ($isRun) {
            foreach ($rows as $row) {
                $this->db->insert($table, $row);
            }
        }

        return new ArrayCollection($rows);
    }
}
