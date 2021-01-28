<?php

use Phpmig\Migration\Migration;

class UserAddTableIndexVerifiedMobile extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->createIndex('user', 'verifiedMobile', 'verifiedMobile');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->dropIndex('user', 'verifiedMobile');
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column});");
        }
    }

    protected function dropIndex($table, $index)
    {
        if ($this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("DROP INDEX {$index} ON {$table};");
        }
    }

    protected function getBiz()
    {
        return $biz = $this->getContainer();
    }
}
