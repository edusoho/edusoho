<?php

use Phpmig\Migration\Migration;

class BizQuestionAddScoreRule extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_question` ADD COLUMN `score_rule` text COMMENT '得分规则';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_question` DROP COLUMN `score_rule`;');
    }
}
