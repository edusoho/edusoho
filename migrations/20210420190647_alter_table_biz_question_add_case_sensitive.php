<?php

use Phpmig\Migration\Migration;

class AlterTableBizQuestionAddCaseSensitive extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_question` ADD `case_sensitive` TINYINT DEFAULT 1 COMMENT '填空题判题大小写敏感' AFTER `answer_mode`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_question` DROP COLUMN `case_sensitive`;");
    }
}
