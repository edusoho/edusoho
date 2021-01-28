<?php

namespace Codeages\Biz\Framework\Testing;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DbTestHelper
{
    /**
     * @var Connection
     */
    protected $db;

    public function __construct($db)
    {
        if ($db instanceof Connection) {
            $this->db = $db;
        } elseif (is_array($db)) {
            $this->db = DriverManager::getConnection($db);
        }
    }

    /**
     * 清除所有表的数据，表名前缀为`migration`的表除外。
     */
    public function truncateAllTables()
    {
        $schema = $this->db->getSchemaManager();
        $tableNames = $schema->listTableNames();
        foreach ($tableNames as $tableName) {
            if (0 === \strpos($tableName, 'migration')) {
                continue;
            }
            $sql = "TRUNCATE {$tableName}";
            $this->db->exec($sql);
        }
    }
}
