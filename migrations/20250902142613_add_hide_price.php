<?php

use Phpmig\Migration\Migration;

class AddHidePrice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_v8` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格';
            ALTER TABLE `classroom` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格';
            ALTER TABLE `item_bank_exercise` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
           ALTER TABLE `course_v8` DROP COLUMN `hidePrice`;
           ALTER TABLE `classroom` DROP COLUMN `hidePrice`;
           ALTER TABLE `item_bank_exercise` DROP COLUMN `hidePrice`;
        ');
    }
}
