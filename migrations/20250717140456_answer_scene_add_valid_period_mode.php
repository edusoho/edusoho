<?php

use Phpmig\Migration\Migration;

class AnswerSceneAddValidPeriodMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `valid_period_mode` tinyint(1)  NOT NULL DEFAULT 0 COMMENT '有效期模式，目前只存3一种(固定考试时间)'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_answer_scene` DROP COLUMN `valid_period_mode`');
    }
}
