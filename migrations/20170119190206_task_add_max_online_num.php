<?php

use Phpmig\Migration\Migration;

class TaskAddMaxOnlineNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "ALTER TABLE `course_task` ADD `maxOnlineNum` INT(11) UNSIGNED DEFAULT 0 COMMENT '任务最大可同时进行的人数，0为不限制';";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "ALTER TABLE `course_task` DROP COLUMN  `maxOnlineNum`;";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }
}
