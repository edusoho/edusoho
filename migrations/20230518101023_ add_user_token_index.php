<?php

use Phpmig\Migration\Migration;

class AddUserTokenIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $connection = $this->getContainer()['db'];
        if (!$this->isIndexExist('user_token', 'userid_type_idx')) {
            $connection->exec("ALTER TABLE `user_token` ADD INDEX `userid_type_idx` (`userId`, `type`(10));");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $connection = $this->getContainer()['db'];
        if ($this->isIndexExist('user_token', 'userid_type_idx')) {
            $connection->exec('ALTER TABLE `user_token` DROP INDEX `userid_type_idx`;');
        }

    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
