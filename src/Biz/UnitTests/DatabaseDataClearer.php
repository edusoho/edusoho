<?php

namespace Biz\UnitTests;

class DatabaseDataClearer
{
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function clear()
    {
        $tableNames = $this->db->getSchemaManager()->listTableNames();
        $this->clearWithTablenames($tableNames);
    }

    public function clearQuickly()
    {
        $tableNames = $this->db->getInsertedTables();
        $tableNames = array_unique($tableNames);
        $this->clearWithTablenames($tableNames);
    }

    private function clearWithTablenames(array $tableNames)
    {
        $sql = '';

        foreach ($tableNames as $tableName) {
            if ('migrations' == $tableName) {
                continue;
            }

            $sql .= "TRUNCATE {$tableName};";
        }

        if (!empty($sql)) {
            $this->db->exec($sql);
            $this->db->resetInsertedTables();
        }
    }
}
