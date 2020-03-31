<?php

use Phpmig\Migration\Migration;

class QuestionBankAddItemBankId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("alter table `question_bank` add column `itemBankId` int(10) not null comment '标准题库id'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec("alter table `question_bank` drop column `itemBankId`;");
    }
}
