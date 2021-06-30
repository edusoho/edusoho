<?php

use Phpmig\Migration\Migration;

class AlterBizWrongQuestion extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_wrong_question` ADD `testpaper_id` int(11) unsigned NOT NULL COMMENT '考试试卷ID' AFTER `answer_scene_id`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_wrong_question` DROP COLUMN `testpaper_id`;');
    }
}
