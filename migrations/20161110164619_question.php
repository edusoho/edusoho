<?php

use Phpmig\Migration\Migration;

class Question extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE question add courseId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `target`
        ");

        $db->exec("
            ALTER TABLE question add lessonId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `courseId`
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `question` DROP `courseId`');
        $db->exec('ALTER TABLE `question` DROP `lessonId`');
    }
}
