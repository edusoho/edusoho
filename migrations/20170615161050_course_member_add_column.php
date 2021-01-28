<?php

use Phpmig\Migration\Migration;

class CourseMemberAddColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
          ALTER TABLE `course_member` ADD `learnedRequiredNum` INT(10) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'已学习的必修任务数量\' AFTER `learnedNum`;
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
         ALTER TABLE `course_member` DROP `learnedRequiredNum`;
        ');
    }
}
