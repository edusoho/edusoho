<?php

use Phpmig\Migration\Migration;

class QuestionBankAddItemBankId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("ALTER TABLE `question_bank` ADD COLUMN `itemBankId` INT(10) NOT NULL comment '标准题库id'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('ALTER TABLE `question_bank` DROP COLUMN `itemBankId`;');
    }
}
