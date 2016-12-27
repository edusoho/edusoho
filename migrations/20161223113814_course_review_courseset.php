<?php

use Phpmig\Migration\Migration;

class CourseReviewCourseset extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE course_review add COLUMN courseSetId int(10) UNSIGNED NOT NULL DEFAULT '0';
        ");

        $db->exec("ALTER TABLE c2_course ADD ratingNum int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评论数';");
        $db->exec("ALTER TABLE c2_course ADD rating float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程计划评分'");

        $db->exec("ALTER TABLE c2_course_set ADD ratingNum int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评论数';");
        $db->exec("ALTER TABLE c2_course_set ADD rating float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评分'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE `course_review` DROP COLUMN `courseSetId`;");
        $db->exec("ALTER TABLE `c2_course` DROP COLUMN `ratingNum`;");
        $db->exec("ALTER TABLE `c2_course` DROP COLUMN `rating`;");
        $db->exec("ALTER TABLE `c2_course_set` DROP COLUMN `ratingNum`;");
        $db->exec("ALTER TABLE `c2_course_set` DROP COLUMN `rating`;");
    }
}
