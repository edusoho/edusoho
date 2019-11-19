<?php

use Phpmig\Migration\Migration;

class QuestionAddBankId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `question` ADD `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id' AFTER `categoryId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `question` DROP column `bankId`;');
    }
}
