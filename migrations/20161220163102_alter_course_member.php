<?php

use Phpmig\Migration\Migration;

class AlterCourseMember extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec(" ALTER TABLE `course_member` ADD `lastViewTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 
                  '最后查看时间' ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `course_member` DROP COLUMN `lastViewTime`');
    }
}
