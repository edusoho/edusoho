<?php

use Phpmig\Migration\Migration;

class CourseTaskResultAddLastLearnTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_task_result` ADD `lastLearnTime` int(10) DEFAULT 0 COMMENT '最后学习时间' AFTER `status`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
