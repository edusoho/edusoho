<?php

use Phpmig\Migration\Migration;

class QuestionAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `question` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0';");

        $biz['db']->exec('UPDATE `question` SET `courseSetId` = `courseId`;');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `question` DROP COLUMN `courseSetId`;');
    }
}
