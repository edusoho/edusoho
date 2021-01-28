<?php

use Phpmig\Migration\Migration;

class QuestionAddUpdatedUserId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE question ADD COLUMN updatedUserId int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER userId");
        $db->exec('UPDATE question SET updatedUserId = userId');
        $db->exec("ALTER TABLE question CHANGE `userId` `createdUserId` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE question DROP COLUMN updatedUserId');
        $db->exec("ALTER TABLE question CHANGE `createdUserId` `userId` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
    }
}
