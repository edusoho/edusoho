<?php

use Phpmig\Migration\Migration;

class AlterItemCategoryAddQuestionNumItemNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_item_category` ADD `item_num` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目总数' AFTER `bank_id`, ADD `question_num` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题总数' AFTER `item_num`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
