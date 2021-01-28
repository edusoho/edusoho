<?php

use Phpmig\Migration\Migration;

class UserActive extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE user_active_log DROP INDEX createdTime;
                ALTER TABLE user_active_log ADD INDEX `userId_createdTime` (`userId`,`createdTime`);
                ALTER TABLE user_active_log ADD INDEX `createdTime`(`createdTime`)');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('
                ALTER TABLE user_active_log DROP INDEX `userId_createdTime`;
                ALTER TABLE user_active_log DROP INDEX `createdTime`;');
    }
}
