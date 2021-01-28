<?php

use Phpmig\Migration\Migration;

class AddCourseRecommondField extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD COLUMN `recommended` tinyint(3) unsigned  NOT NULL DEFAULT '0' COMMENT '是否为推荐课程'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD COLUMN `recommendedSeq`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD COLUMN `recommendedTime`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `c2_course` DROP COLUMN `recommended`;');
        $biz['db']->exec('ALTER TABLE `c2_course` DROP COLUMN `recommendedSeq`;');
        $biz['db']->exec('ALTER TABLE `c2_course` DROP COLUMN `recommendedTime`;');
    }
}
