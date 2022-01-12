<?php

use Phpmig\Migration\Migration;

class ActivityHomeworkFinish extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `activity_testpaper` ADD COLUMN `comment_data` text COMMENT '评语详情数据（json格式）' after `finishCondition`;
            ALTER TABLE `activity_homework` ADD COLUMN `finish_condition` text COMMENT '完成条件' after `assessmentId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `activity_testpaper` DROP COLUMN `comment_data`;
        ');
    }
}
