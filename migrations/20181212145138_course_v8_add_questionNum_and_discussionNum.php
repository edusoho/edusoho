<?php

use Phpmig\Migration\Migration;

class CourseV8AddQuestionNumAndDiscussionNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_v8` ADD `discussionNum` int(11) DEFAULT 0 COMMENT '话题数' AFTER `noteNum`;");
        $db->exec("ALTER TABLE `course_v8` ADD `questionNum` int(11) DEFAULT 0 COMMENT '问题数' AFTER `noteNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_v8` DROP column `discussionNum`;');
        $db->exec('ALTER TABLE `course_v8` DROP column `questionNum`;');
    }
}
