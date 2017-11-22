<?php

use Phpmig\Migration\Migration;

class DropUserBindIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE user_bind DROP INDEX type_2");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `user_bind` ADD UNIQUE `type_2` (`type`, `toId`);");
    }
}
