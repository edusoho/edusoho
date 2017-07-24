<?php

use Phpmig\Migration\Migration;

class PublishedTaskNumChange extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_v8` CHANGE `publishedTaskNum` `compulsoryTaskNum` INT(10) NULL DEFAULT \'0\' COMMENT \'必修任务数\';');
        $db->exec('ALTER TABLE `course_member` CHANGE `learnedRequiredNum` `learnedCompulsoryTaskNum` INT(10) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'已学习的必修任务数量\';');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
