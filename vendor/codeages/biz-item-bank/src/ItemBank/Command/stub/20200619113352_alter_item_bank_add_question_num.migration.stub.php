<?php

use Phpmig\Migration\Migration;

class AlterItemBankAddQuestionNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_item_bank` ADD `question_num` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题总数' AFTER `item_num`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
