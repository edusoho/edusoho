<?php

namespace Codeages\Biz\Framework\UnitTests;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected static $biz;

    public static function setUpBeforeClass()
    {
    }

    public function setUp()
    {
        self::emptyDatabase();
    }

    public static function setBiz($biz)
    {
        self::$biz = $biz;
    }

    public static function emptyDatabase($all = false)
    {
        $db = self::$biz['db'];

        if ($all) {
            $tableNames = $db->getSchemaManager()->listTableNames();
        } else {
            $tableNames = $db->getInsertedTables();
            $tableNames = array_unique($tableNames);
        }

        $sql = '';

        foreach ($tableNames as $tableName) {
            if ($tableName == 'migrations') {
                continue;
            }

            $sql .= "TRUNCATE {$tableName};";
        }

        if (!empty($sql)) {
            $db->exec($sql);
            $db->resetInsertedTables();
        }
    }
}
