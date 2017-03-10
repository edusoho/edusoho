<?php

use Phpmig\Migration\Migration;

class CourseReviewCourseset extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE course_review add COLUMN courseSetId int(10) UNSIGNED NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `course_review` DROP COLUMN `courseSetId`;');
    }
}
