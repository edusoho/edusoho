<?php

use Phpmig\Migration\Migration;

class WrongQuestionAddHasAnswer extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `biz_wrong_question` ADD COLUMN `has_answer` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否作答' AFTER `submit_time`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `biz_wrong_question` DROP COLUMN `has_answer`;
        ");
    }
}
